<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Client;

use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\Amount;
use Baywall\Core\Domain\ValueObject\BlockNumber;
use Baywall\Core\Domain\ValueObject\BlockTag;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\Hex;
use Baywall\Core\Infrastructure\Web3\BlockchainRetryer;
use Baywall\Core\Infrastructure\Web3\ValueObject\EthBlock;
use Baywall\Core\Domain\ValueObject\RpcUrl;

class BlockchainClient {
	public function __construct( RpcUrl $rpc_url, ?RpcTransport $transport = null ) {
		$this->retryer    = new BlockchainRetryer();
		$this->rpc_client = new RpcClient( $rpc_url, $transport );
	}
	private readonly BlockchainRetryer $retryer;
	private readonly RpcClient $rpc_client;

	/**
	 * `eth_chainId`を呼び出します
	 */
	public function ethChainId(): ChainId {
		/** @var ChainId|null */
		$chain_id = null;
		$this->retryer->execute(
			function () use ( &$chain_id ) {
				$result   = $this->rpc_client->request( 'eth_chainId' );
				$chain_id = ChainId::from( Hex::from( $result )->intValue() );
			}
		);
		assert( ! is_null( $chain_id ), '[1BAA2783] Failed to get chain ID.' );

		return $chain_id;
	}

	/**
	 * `eth_getBlockByNumber`を呼び出します。
	 */
	public function ethGetBlockByNumber( BlockNumber|BlockTag $block_number_or_tag ): EthBlock {
		if ( $block_number_or_tag instanceof BlockNumber ) {
			$block_number = $block_number_or_tag->hex()->value();
		} else {
			$block_number = $block_number_or_tag->value();
		}

		/** @var null|EthBlock */
		$result = null;
		$this->retryer->execute(
			function () use ( $block_number, &$result ) {
				$block  = $this->rpc_client->request( 'eth_getBlockByNumber', array( $block_number, false ) );
				$result = EthBlock::from( $block );
			}
		);
		assert( null !== $result, '[F6805A68] Result should not be null after retry.' );

		return $result;
	}

	/**
	 * `eth_blockNumber`を呼び出します。
	 *
	 * 最新(latest)のブロック番号を取得します。
	 */
	public function ethBlockNumber(): BlockNumber {
		/** @var BlockNumber|null */
		$block_number = null;
		$this->retryer->execute(
			function () use ( &$block_number ) {
				$result       = $this->rpc_client->request( 'eth_blockNumber' );
				$block_number = BlockNumber::fromHex( Hex::from( $result ) );
			}
		);

		return $block_number;
	}

	/**
	 * `eth_getBalance`を呼び出します。
	 *
	 * アカウントの残高を取得します。
	 */
	public function ethGetBalance( Address $address ): Amount {
		/** @var Amount|null */
		$balance = null;
		$this->retryer->execute(
			function () use ( $address, &$balance ) {
				$result  = $this->rpc_client->request( 'eth_getBalance', array( $address->value(), BlockTag::latest()->value() ) );
				$balance = Amount::fromHex( Hex::from( $result ) );
			}
		);

		return $balance;
	}

	/**
	 * `eth_getLogs`を呼び出します。
	 *
	 * @param array<string, mixed> $filter フィルタ（fromBlock / toBlock / address / topics）
	 * @return \stdClass[] ログの配列。応答が配列でない場合は空配列
	 */
	public function ethGetLogs( array $filter ): array {
		$logs = array();
		$this->retryer->execute(
			function () use ( $filter, &$logs ) {
				$result = $this->rpc_client->request( 'eth_getLogs', array( $filter ) );
				$logs   = is_array( $result ) ? $result : array();
			}
		);

		return $logs;
	}
}
