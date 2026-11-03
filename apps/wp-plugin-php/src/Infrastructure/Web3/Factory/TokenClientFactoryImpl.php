<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Factory;

use Baywall\Core\Application\Port\TokenClientFactory;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\RpcUrl;
use Baywall\Core\Infrastructure\Web3\Client\TokenClientImpl;

class TokenClientFactoryImpl implements TokenClientFactory {
	/**
	 * トークンクライアントのインスタンスを生成します。
	 */
	public function create( RpcUrl $rpc_url, Address $contract_address ): TokenClientImpl {
		return new TokenClientImpl( $rpc_url, $contract_address );
	}
}
