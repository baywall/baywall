<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Factory;

use Baywall\Core\Domain\ValueObject\RpcUrl;
use Baywall\Core\Infrastructure\Web3\Client\EnsV2UniversalResolverClient;

class EnsV2UniversalResolverClientFactory {

	/**
	 * 指定した RPC エンドポイントに接続する ENSv2 Universal Resolver クライアントを生成します。
	 *
	 * @param RpcUrl $rpc_url RPC エンドポイント
	 */
	public function create( RpcUrl $rpc_url ): EnsV2UniversalResolverClient {
		return new EnsV2UniversalResolverClient( $rpc_url );
	}
}
