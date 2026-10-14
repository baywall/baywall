<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Factory;

use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\RpcUrl;
use Baywall\Core\Infrastructure\Web3\Client\TokenClient;

class TokenClientFactory {
	/**
	 * トークンクライアントのインスタンスを生成します。
	 */
	public function create( RpcUrl $rpc_url, Address $contract_address ): TokenClient {
		return new TokenClient( $rpc_url, $contract_address );
	}
}
