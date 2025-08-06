<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Crawler;

use Cornix\Serendipity\Core\Application\Service\ServerSignerService;
use Cornix\Serendipity\Core\Domain\Repository\AppContractRepository;
use Cornix\Serendipity\Core\Infrastructure\Format\HexFormat;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\UnlockPaywallTransactionRepository;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\UnlockPaywallTransferEventRepository;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Infrastructure\Web3\AppContractAbi;
use Cornix\Serendipity\Core\Infrastructure\Web3\Factory\BlockchainClientFactory;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\Amount;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;
use Cornix\Serendipity\Core\Domain\ValueObject\TransactionHash;
use phpseclib\Math\BigInteger;
use stdClass;

/**
 * Appコントラクトのログを取得し、DBに保存するクラス
 */
class AppContractCrawler {
	public function __construct( AppContractAbi $app_abi, UnlockPaywallTransferEventRepository $unlock_paywall_transfer_event_repository, UnlockPaywallTransactionRepository $unlock_paywall_transaction_repository, UnlockPaywallTransferCrawler $unlock_paywall_transfer_crawler ) {
		$this->app_abi                                  = $app_abi;
		$this->unlock_paywall_transfer_event_repository = $unlock_paywall_transfer_event_repository;
		$this->unlock_paywall_transaction_repository    = $unlock_paywall_transaction_repository;
		$this->unlock_paywall_transfer_crawler          = $unlock_paywall_transfer_crawler;
	}
	private AppContractAbi $app_abi;
	private UnlockPaywallTransferEventRepository $unlock_paywall_transfer_event_repository;
	private UnlockPaywallTransactionRepository $unlock_paywall_transaction_repository;
	private UnlockPaywallTransferCrawler $unlock_paywall_transfer_crawler;

	public function crawl( ChainId $chain_id, BlockNumber $from_block, BlockNumber $to_block ): void {
		// UnlockPaywallTransferイベントのログを取得
		$transfer_logs = $this->getUnlockPaywallTransferLogs( $chain_id, $from_block, $to_block );
		// トランザクション情報をDBに保存
		$this->saveUnlockPaywallTransaction( $chain_id, $transfer_logs );
		// UnlockPaywallTransferイベントのログをDBに保存
		$this->saveUnlockPaywallTransfer( $transfer_logs );
	}

	/**
	 * UnlockPaywallTransferイベントのログを取得します。
	 */
	private function getUnlockPaywallTransferLogs( ChainId $chain_id, BlockNumber $from_block, BlockNumber $to_block ): array {
		return $this->unlock_paywall_transfer_crawler->execute( $chain_id, $from_block, $to_block );
	}

	/**
	 * UnlockPaywallTransferイベントが発生した時のトランザクション情報をDBに保存します。
	 */
	private function saveUnlockPaywallTransaction( ChainId $chain_id, array $unlock_paywall_transfer_logs ): void {
		/** @var string[] */
		$saved_invoice_id_hex_array = array(); // DBに保存済みのinvoiceIDのリスト(DBへのアクセス回数を減らすために使用)

		foreach ( $unlock_paywall_transfer_logs as $unlock_paywall_transfer_log ) {
			$event_args = $this->app_abi->decodeEventParameters( $unlock_paywall_transfer_log );
			assert( is_array( $event_args ), '[80A37466] event_args is not array' );
			/** @var BigInteger */
			$invoice_id_bi = $event_args['invoiceID'];
			assert( $invoice_id_bi instanceof BigInteger, '[9A2B802E] invoice_id is not BigInteger. ' . var_export( $invoice_id_bi, true ) );
			$invoice_id = InvoiceId::from( $invoice_id_bi );

			// 既に保存済みのinvoiceIDの場合はスキップ
			if ( in_array( $invoice_id->hex(), $saved_invoice_id_hex_array, true ) ) {
				continue;
			} else {
				$saved_invoice_id_hex_array[] = $invoice_id->hex();
			}

			$transaction_hash = TransactionHash::from( $unlock_paywall_transfer_log->transactionHash );
			/** @var string */
			$block_number_hex = $unlock_paywall_transfer_log->blockNumber;
			assert( Validate::isHex( $block_number_hex ), '[067CCE00] blockNumber is not hex. ' . var_export( $block_number_hex, true ) );

			$this->unlock_paywall_transaction_repository->save(
				$invoice_id,
				$chain_id,
				BlockNumber::from( $block_number_hex ),
				$transaction_hash,
			);
		}
	}

	/**
	 * UnlockPaywallTransferイベントのログをDBに保存します。
	 */
	private function saveUnlockPaywallTransfer( array $unlock_paywall_transfer_logs ): void {

		foreach ( $unlock_paywall_transfer_logs as $unlock_paywall_transfer_log ) {
			$event_args = $this->app_abi->decodeEventParameters( $unlock_paywall_transfer_log );
			assert( is_array( $event_args ), '[66C28129] event_args is not array' );

			// イベント発行時の引数を取得
			$from          = Address::from( $event_args['from'] );
			$to            = Address::from( $event_args['to'] );
			$token_address = Address::from( $event_args['token'] );
			/** @var BigInteger */
			$amount = $event_args['amount'];
			/** @var BigInteger */
			$invoice_id_bi = $event_args['invoiceID'];
			/** @var BigInteger */
			$transfer_type = $event_args['transferType'];

			/** @var string */
			$log_index_hex = $unlock_paywall_transfer_log->logIndex;

			$this->unlock_paywall_transfer_event_repository->save(
				InvoiceId::from( $invoice_id_bi ),
				HexFormat::toInt( $log_index_hex ),
				$from,
				$to,
				$token_address,
				Amount::from( $amount->toString() ),
				HexFormat::toInt( '0x' . $transfer_type->toHex() ),
			);
		}
	}
}

// --------------------------------------------------------------------------------

/**
 * このサーバーに関係する`UnlockPaywallTransfer`イベントのログを取得するクラス
 */
class UnlockPaywallTransferCrawler {

	public function __construct( AppContractRepository $app_contract_repository, ServerSignerService $server_signer_service, BlockchainClientFactory $blockchain_client_factory ) {
		// UnlockPaywallTransferイベントのtopic
		$topic_hash = ( new AppContractAbi() )->topicHash( 'UnlockPaywallTransfer' );

		// サーバーの署名用ウォレットアドレス
		$server_signer                 = $server_signer_service->getServerSigner();
		$server_signer_address_bytes32 = $server_signer->address()->toBytes32Hex();

		$this->topics                    = array(
			$topic_hash,
			$server_signer_address_bytes32,
		);
		$this->app_contract_repository   = $app_contract_repository;
		$this->blockchain_client_factory = $blockchain_client_factory;
	}

	private array $topics;
	private AppContractRepository $app_contract_repository;
	private BlockchainClientFactory $blockchain_client_factory;

	/**
	 * このサーバーに関係するUnlockPaywallTransferイベントのログを取得します。
	 *
	 * @return stdClass[]
	 */
	public function execute( ChainId $chain_id, BlockNumber $from_block, BlockNumber $to_block ): array {
		$blockchain_client = $this->blockchain_client_factory->create( $chain_id );

		/** @var array|null */
		$logs_result = null;
		$blockchain_client->getLogs(
			array(
				'fromBlock' => $from_block->hex(),
				'toBlock'   => $to_block->hex(),
				'address'   => $this->app_contract_repository->get( $chain_id )->address()->value(),
				'topics'    => $this->topics,
			),
			function ( $err, $logs ) use ( &$logs_result ) {
				if ( $err ) {
					throw $err;
				}
				$logs_result = $logs;
			}
		);
		return $logs_result;
	}
}
