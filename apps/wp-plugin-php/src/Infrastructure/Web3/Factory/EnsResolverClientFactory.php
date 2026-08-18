<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Factory;

use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\RpcUrl;
use Baywall\Core\Infrastructure\Web3\Client\EnsResolverClient;

class EnsResolverClientFactory {

	/**
	 * 指定した RPC エンドポイントと Resolver アドレスに接続する ENS Resolver クライアントを生成します。
	 *
	 * @param RpcUrl  $rpc_url RPC エンドポイント
	 * @param Address $address Resolver コントラクトのアドレス
	 */
	public function create( RpcUrl $rpc_url, Address $address ): EnsResolverClient {
		return new EnsResolverClient( $rpc_url, $address );
	}
}
