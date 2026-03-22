<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3;

use Cornix\Serendipity\Core\Domain\Entity\AppContract;
use Cornix\Serendipity\Core\Infrastructure\Web3\Abi\AppContractAbi;
use Cornix\Serendipity\Core\Infrastructure\Web3\ValueObject\GetPaywallStatusResult;
use Cornix\Serendipity\Core\Infrastructure\Web3\ValueObject\UnlockPaywallTransferEvent;
use Cornix\Serendipity\Core\Infrastructure\Web3\BlockchainClient;
use Cornix\Serendipity\Core\Infrastructure\Web3\Factory\ContractFactory;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Domain\ValueObject\Hex;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;
use Cornix\Serendipity\Core\Domain\ValueObject\PostId;
use phpseclib\Math\BigInteger;
use Web3\Contract;

class AppContractClient {

	public function __construct( AppContract $app_contract, ?AppContractAbi $app_contract_abi = null ) {
		assert( $app_contract->chain()->connectable(), '[A5ED369D]' );   // 接続可能なチェーンであること

		$this->app_contract      = $app_contract;
		$this->abi               = $app_contract_abi ?? new AppContractAbi();
		$this->contract          = ( new ContractFactory() )->create(
			$app_contract->chain()->rpcUrl(),
			$this->abi->get(),
			$app_contract->address()
		);
		$this->blockchain_client = new BlockchainClient( $app_contract->chain()->rpcUrl() );
	}
	private Contract $contract;
	private AppContractAbi $abi;
	private AppContract $app_contract;
	private BlockchainClient $blockchain_client;

	protected function contract(): Contract {
		return $this->contract;
	}

	public function getPaywallStatus( Address $signer_address, PostId $post_id, Address $customer_address ): GetPaywallStatusResult {

		/** @var GetPaywallStatusResult|null */
		$result = null;
		$this->contract->call(
			'getPaywallStatus',
			$signer_address->value(),
			$post_id->value(),
			$customer_address->value(),
			function ( $err, $res ) use ( &$result ) {
				if ( $err ) {
					throw $err;
				}

				$is_unlocked           = $res['isUnlocked'];
				$invoice_id            = $res['invoiceId'];
				$unlocked_block_number = $res['unlockedBlockNumber'];

				assert( is_bool( $is_unlocked ) );
				assert( $invoice_id instanceof BigInteger );
				assert( $unlocked_block_number instanceof BigInteger );

				$result = new GetPaywallStatusResult(
					$is_unlocked,
					$is_unlocked ? InvoiceId::fromHex( Hex::from( '0x' . $invoice_id->toHex() ) ) : null,
					$is_unlocked ? BlockNumber::fromHex( Hex::from( '0x' . $unlocked_block_number->toHex() ) ) : null
				);
			}
		);

		assert( ! is_null( $result ) );
		return $result;
	}

	/**
	 * ペイウォール解除時のトークン転送イベントを取得します。
	 *
	 * @param BlockNumber $from_block 開始ブロック番号
	 * @param BlockNumber $to_block   終了ブロック番号
	 * @param Address     $server_signer_address サーバーの署名用ウォレットアドレス
	 * @return UnlockPaywallTransferEvent[] イベントの配列
	 */
	public function getUnlockPaywallTransferEvents(
		BlockNumber $from_block,
		BlockNumber $to_block,
		Address $server_signer_address
	) {
		assert( $from_block->compare( $to_block ) <= 0, '[438F5DEE] from_block must be less than or equal to to_block.' );

		$filter = array(
			'fromBlock' => $from_block->hex()->value(),
			'toBlock'   => $to_block->hex()->value(),
			'address'   => $this->app_contract->address()->value(),
			'topics'    => array(
				$this->abi->unlockPaywallTransferTopicHash(),
				$server_signer_address->toBytes32Hex(),
			),
		);

		/** @var UnlockPaywallTransferEvent[] */
		$results = array();
		$this->blockchain_client->ethGetLogs(
			$filter,
			function ( $err, $logs ) use ( &$results ) {
				if ( $err ) {
					throw $err;
				}
				// $logsがnullの可能性があるため、nullの場合は空配列に置き換える
				// [ERROR] TypeError: Argument 2 passed to Cornix\\Serendipity\\Core\\Infrastructure\\Web3\\AppContractClient::Cornix\\Serendipity\\Core\\Infrastructure\\Web3\\{closure}() must be of the type array, null given
				if ( is_null( $logs ) ) {
					$logs = array();
				}

				$results = array();
				/** @var \stdClass[] $logs */
				foreach ( $logs as $log ) {
					$results[] = $this->abi->decodeUnlockPaywallTransferEvent( $log );
				}
			}
		);

		return $results;
	}
}
