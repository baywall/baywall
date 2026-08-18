<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Factory;

use Baywall\Core\Domain\ValueObject\RpcUrl;
use Baywall\Core\Infrastructure\Web3\Client\EnsRegistryClient;

class EnsRegistryClientFactory {

	/**
	 * 指定した RPC エンドポイントに接続する ENS レジストリクライアントを生成します。
	 */
	public function create( RpcUrl $rpc_url ): EnsRegistryClient {
		return new EnsRegistryClient( $rpc_url );
	}
}
