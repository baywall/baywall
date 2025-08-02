<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3\Factory;

use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use Cornix\Serendipity\Core\Infrastructure\Web3\BlockchainClient;

class BlockchainClientFactory {

	public function __construct( ChainRepository $chain_repository ) {
		$this->chain_repository = $chain_repository;
	}
	private ChainRepository $chain_repository;

	/**
	 * 指定したチェーンに接続するオブジェクトを生成します。
	 */
	public function create( ChainID $chain_ID ): BlockchainClient {
		// チェーンに接続するためのRPC URLを取得
		$chain   = $this->chain_repository->get( $chain_ID );
		$rpc_url = $chain->rpcURL();
		if ( is_null( $rpc_url ) ) {
			throw new \Exception( '[4513DF1F] RPC URL is not found. - ' . $chain_ID );
		}

		return new BlockchainClient( $rpc_url );
	}
}
