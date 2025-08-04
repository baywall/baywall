<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Service\ChainService;
use Cornix\Serendipity\Core\Application\Service\ServerSignerService;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Domain\Repository\AppContractRepository;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceRepository;
use Cornix\Serendipity\Core\Domain\Repository\PostRepository;
use Cornix\Serendipity\Core\Infrastructure\Web3\AppContractClient;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockTag;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;
use Cornix\Serendipity\Core\Infrastructure\Web3\Factory\BlockchainClientFactory;

class RequestPaidContentByNonceResolver extends ResolverBase {

	public function __construct(
		AppContractRepository $app_contract_repository,
		ChainService $chain_service,
		InvoiceRepository $invoice_repository,
		PostRepository $post_repository,
		ServerSignerService $server_signer_service,
		UserAccessChecker $user_access_checker,
		BlockchainClientFactory $blockchain_client_factory
	) {
		$this->app_contract_repository   = $app_contract_repository;
		$this->chain_service             = $chain_service;
		$this->invoice_repository        = $invoice_repository;
		$this->post_repository           = $post_repository;
		$this->server_signer_service     = $server_signer_service;
		$this->user_access_checker       = $user_access_checker;
		$this->blockchain_client_factory = $blockchain_client_factory;
	}

	private AppContractRepository $app_contract_repository;
	private ChainService $chain_service;
	private InvoiceRepository $invoice_repository;
	private PostRepository $post_repository;
	private ServerSignerService $server_signer_service;
	private UserAccessChecker $user_access_checker;
	private BlockchainClientFactory $blockchain_client_factory;

	// ここの定数は、GraphQLのエラーコードと一致させること
	private const ERROR_CODE_INVALID_NONCE           = 'INVALID_NONCE';
	private const ERROR_CODE_INVALID_CHAIN_ID        = 'INVALID_CHAIN_ID';
	private const ERROR_CODE_PAYWALL_LOCKED          = 'PAYWALL_LOCKED';
	private const ERROR_CODE_TRANSACTION_UNCONFIRMED = 'TRANSACTION_UNCONFIRMED';

	/**
	 * #[\Override]
	 *
	 * @return string|null
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var string */
		$invoice_id_hex = $args['invoiceID'];
		/** @var string */
		$nonce = $args['nonce'];

		Validate::checkHex( $invoice_id_hex );
		Validate::checkInvoiceNonceValueFormat( $nonce );
		$invoice_id = InvoiceId::from( $invoice_id_hex );

		// エラー時の結果を返すコールバック関数
		$error_result_callback = fn( $error_code ) => array(
			'content'   => null,
			'errorCode' => $error_code,
		);

		$invoice = $this->invoice_repository->get( $invoice_id );
		if ( is_null( $invoice ) ) {
			// 通常、ここは通らない
			throw new \Exception( '[D2AAA3B6] Invoice data not found. invoiceID: ' . $invoice_id_hex );
		}

		$db_nonce = $invoice->nonce(); // DBから取得したnonce
		if ( is_null( $db_nonce ) || $nonce !== $db_nonce->value() ) {
			// nonceが無効な場合はドメインエラーとして返す
			return $error_result_callback( self::ERROR_CODE_INVALID_NONCE );
		}

		$post_id          = $invoice->postId();
		$chain            = $this->chain_service->getChain( $invoice->chainId() );
		$consumer_address = $invoice->consumerAddress();

		// 投稿を閲覧できる権限があることをチェック
		$this->user_access_checker->checkCanViewPost( $post_id->value() );

		if ( ! $chain->connectable() ) {
			// 指定されたチェーンIDが接続可能でない場合はドメインエラーとして返す
			// ※ 支払い後、管理者によってチェーンが無効化された場合はここを通るため、例外を投げない
			return $error_result_callback( self::ERROR_CODE_INVALID_CHAIN_ID );
		}

		// ブロックチェーンに問い合わせる
		$app_contract   = $this->app_contract_repository->get( $chain->id() );
		$app            = new AppContractClient( $app_contract );
		$server_signer  = $this->server_signer_service->getServerSigner();
		$payment_status = $app->getPaywallStatus( $server_signer->address(), $post_id, $consumer_address );

		if ( ! $payment_status->isUnlocked() ) {
			// 最新のブロックでもペイウォールの解除が確認できなかった場合
			return $error_result_callback( self::ERROR_CODE_PAYWALL_LOCKED );
		} elseif ( ! $this->isConfirmed( $chain->id(), $payment_status->unlockedBlockNumber() ) ) {
			// 最新のブロックではペイウォールの解除が確認できたが、
			// トランザクションの待機ブロック数が管理者が指定した数を下回っている場合
			return $error_result_callback( self::ERROR_CODE_TRANSACTION_UNCONFIRMED );
		}

		// 有料部分のコンテンツを取得
		$paid_content = $this->post_repository->get( $post_id )->paidContent();
		assert( ! is_null( $paid_content ), '[391C0A77] Paid content should not be null.' );

		return array(
			'content'   => apply_filters( 'the_content', $paid_content->value() ),
			'errorCode' => null,
		);
	}

	/**
	 * トランザクションが待機済みかどうかを判定します。
	 */
	private function isConfirmed( ChainId $chain_id, BlockNumber $unlocked_block_number ): bool {
		// トランザクションの待機ブロック数を取得
		$chain               = $this->chain_service->getChain( $chain_id );
		$confirmations_value = $chain->confirmations()->value();

		if ( is_int( $confirmations_value ) ) {
			// 最新のブロック番号を取得
			$latest_block        = $this->blockchain_client_factory->create( $chain->id() )->getBlockByNumber( BlockTag::latest() );
			$latest_block_number = $latest_block->number();
			// 基準となるブロック番号を計算(「ペイウォール解除時のブロック番号」<=「基準ブロック番号」となる場合、待機済み)
			$reference_block = $latest_block_number->sub( max( $confirmations_value - 1, 0 ) );
			return $unlocked_block_number->compare( $reference_block ) <= 0;
		} elseif ( $confirmations_value === 'latest' ) {
			// ペイウォールが解除されたブロック番号が取得できているため、当然待機済みと判定
			return true;
		} elseif ( $confirmations_value === 'finalized' ) {
			// TODO: 未実装
			throw new \Exception( '[8A320100] Finalized block number is not implemented yet.' );
		} else {
			throw new \Exception( '[2251BA42] Invalid confirmations value. confirmations: ' . var_export( $confirmations_value, true ) );
		}
	}
}
