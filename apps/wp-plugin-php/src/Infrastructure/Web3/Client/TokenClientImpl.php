<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Client;

use Baywall\Core\Application\Port\TokenClient;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\Decimals;
use Baywall\Core\Domain\ValueObject\RpcUrl;
use Baywall\Core\Domain\ValueObject\Symbol;
use Baywall\Core\Infrastructure\Web3\Abi\TokenAbi;

class TokenClientImpl implements TokenClient {
	private readonly EthCallClient $client;
	private readonly Address $address;

	public function __construct( RpcUrl $rpc_url, Address $contract_address ) {
		$this->client  = new EthCallClient( $rpc_url );
		$this->address = $contract_address;
	}

	/**
	 * トークンの小数点以下桁数を取得します。
	 */
	public function decimals(): Decimals {
		$res = $this->client->call( $this->address, ( new TokenAbi() )->get(), 'decimals' );

		return Decimals::from( (int) $res[0] );
	}

	/**
	 * トークンの通貨シンボルを取得します。
	 */
	public function symbol(): Symbol {
		$res = $this->client->call( $this->address, ( new TokenAbi() )->get(), 'symbol' );

		assert( is_string( $res[0] ?? null ), '[20FD9BCD] symbol is not string.' );
		assert( 0 < strlen( $res[0] ), '[6F010DF1] symbol is empty.' );

		return Symbol::from( $res[0] );
	}
}
