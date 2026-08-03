<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3\Factory;

use Cornix\Serendipity\Core\Domain\ValueObject\RpcUrl;
use Cornix\Serendipity\Core\Infrastructure\Web3\Client\EnsRegistryClient;

class EnsRegistryClientFactory {

	/**
	 * 指定した RPC エンドポイントに接続する ENS レジストリクライアントを生成します。
	 */
	public function create( RpcUrl $rpc_url ): EnsRegistryClient {
		return new EnsRegistryClient( $rpc_url );
	}
}
