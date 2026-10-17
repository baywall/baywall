<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Client;

use Baywall\Core\Domain\Entity\AppContract;
use Baywall\Core\Domain\Service\BlockchainService;
use Baywall\Core\Infrastructure\Web3\Abi\AppContractAbi;
use Baywall\Core\Infrastructure\Web3\ValueObject\GetPaywallStatusResult;
use Baywall\Core\Infrastructure\Web3\ValueObject\UnlockPaywallTransferEvent;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\BlockNumber;
use Baywall\Core\Domain\ValueObject\Hex;
use Baywall\Core\Domain\ValueObject\InvoiceId;
use Baywall\Core\Domain\ValueObject\PostId;

class AppContractClient {

	public function __construct(
		AppContract $app_contract,
		BlockchainService $blockchain_service,
		AppContractAbi $app_contract_abi = new AppContractAbi()
	) {
		assert( $app_contract->chain()->connectable(), '[A5ED369D]' );   // 接続可能なチェーンであること

		$this->app_contract       = $app_contract;
		$this->blockchain_service = $blockchain_service;
		$this->abi                = $app_contract_abi;
		$this->eth_call_client    = new EthCallClient( $app_contract->chain()->rpcUrl() );
	}
	private readonly EthCallClient $eth_call_client;
	private readonly AppContractAbi $abi;
	private readonly AppContract $app_contract;
	private readonly BlockchainService $blockchain_service;

	public function getPaywallStatus( Address $signer_address, PostId $post_id, Address $buyer_address ): GetPaywallStatusResult {

		$res = $this->eth_call_client->call(
			$this->app_contract->address(),
			$this->abi->get(),
			'getPaywallStatus',
			array( $signer_address, $post_id->value(), $buyer_address )
		);

		$is_unlocked  = $res[0];
		$invoice_id   = $res[1];
		$block_number = $res[2];

		assert( is_bool( $is_unlocked ) );

		return new GetPaywallStatusResult(
			$is_unlocked,
			$is_unlocked ? InvoiceId::fromHex( Hex::fromDecValue( $invoice_id ) ) : null,
			$is_unlocked ? BlockNumber::fromHex( Hex::fromDecValue( $block_number ) ) : null
		);
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

		$logs = $this->blockchain_service->getLogs( $this->app_contract->chain()->rpcUrl(), $filter );

		/** @var UnlockPaywallTransferEvent[] */
		$results = array();
		foreach ( $logs as $log ) {
			$results[] = $this->abi->decodeUnlockPaywallTransferEvent( $log );
		}

		return $results;
	}
}
