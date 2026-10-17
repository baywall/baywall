<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Service;

use Baywall\Core\Domain\Service\OracleService;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\Amount;
use Baywall\Core\Domain\ValueObject\Decimals;
use Baywall\Core\Domain\ValueObject\OracleRoundData;
use Baywall\Core\Domain\ValueObject\Rate;
use Baywall\Core\Domain\ValueObject\RpcUrl;
use Baywall\Core\Domain\ValueObject\Symbol;
use Baywall\Core\Domain\ValueObject\SymbolPair;
use Baywall\Core\Infrastructure\Web3\Abi\OracleAbi;
use Baywall\Core\Infrastructure\Web3\Client\EthCallClient;
use Baywall\Core\Infrastructure\Web3\Client\RpcTransport;
use phpseclib4\Math\BigInteger;

class OracleServiceImpl implements OracleService {

	/** テストでトランスポートを差し替えられるようにする（省略時は `HttpRpcTransport`） */
	public function __construct( private readonly ?RpcTransport $rpc_transport = null ) {}

	/** レートの小数点以下桁数を取得します */
	private function decimals( RpcUrl $rpc_url, Address $address ): Decimals {
		$res = $this->call( $rpc_url, $address, 'decimals' );

		return Decimals::from( (int) $res[0] );
	}

	/** オラクルの説明を取得します */
	private function description( RpcUrl $rpc_url, Address $address ): string {
		$res = $this->call( $rpc_url, $address, 'description' );

		assert( is_string( $res[0] ) );

		return $res[0];
	}

	/**
	 * 最新のレートを取得します。
	 * ※ `latestAnswer`は非推奨のため、`latestRoundData`で取得した値を使用
	 */
	private function latestAnswer( RpcUrl $rpc_url, Address $address ): BigInteger {
		return $this->latestRoundData( $rpc_url, $address )->answer();
	}

	/** @inheritdoc */
	public function getRate( RpcUrl $rpc_url, Address $address ): Rate {
		$decimals = $this->decimals( $rpc_url, $address );
		$answer   = $this->latestAnswer( $rpc_url, $address );

		$symbol_pair = $this->symbolPair( $rpc_url, $address );
		$rate_amount = Amount::fromBaseUnitAndDecimals( $answer->toString(), $decimals );

		return Rate::from( $symbol_pair, $rate_amount );
	}

	/**
	 * `description`（登録書式: `BASE/QUOTE`）から通貨ペアを導出します。
	 * 登録経路（`ResolveSaveOracle`）と同じ書式処理で、先頭 2 セグメント（`[0]` = base、`[1]` = quote）を使用し、
	 * 余剰セグメントは無視します。導出不能時は保険として例外で失敗させます（登録経路で到達しない経路）。
	 */
	public function symbolPair( RpcUrl $rpc_url, Address $address ): SymbolPair {
		$description = $this->description( $rpc_url, $address );
		$symbols     = array_map( 'trim', explode( '/', $description ) );

		$base_symbol  = $symbols[0] ?? '';
		$quote_symbol = $symbols[1] ?? '';
		if ( ! $base_symbol || ! $quote_symbol || ! in_array( $quote_symbol, array( 'USD', 'ETH' ), true ) ) {
			throw new \RuntimeException( "[82EC698F] Invalid oracle description. description: {$description}" );
		}

		return SymbolPair::from( Symbol::from( $base_symbol ), Symbol::from( $quote_symbol ) );
	}

	/** 最新のデータを取得します */
	private function latestRoundData( RpcUrl $rpc_url, Address $address ): OracleRoundData {
		$res = $this->call( $rpc_url, $address, 'latestRoundData' );

		return new OracleRoundData(
			new BigInteger( $res[0], 10 ),
			new BigInteger( $res[1], 10 ),
			new BigInteger( $res[2], 10 ),
			new BigInteger( $res[3], 10 ),
			new BigInteger( $res[4], 10 )
		);
	}

	/**
	 * オラクルコントラクトのメソッドを呼び出します。
	 * インスタンスを保持せず、呼び出し毎に `EthCallClient` を生成します。
	 *
	 * @return list<mixed> デコードされた戻り値（位置配列）
	 */
	private function call( RpcUrl $rpc_url, Address $address, string $method ): array {
		$client = new EthCallClient( $rpc_url, $this->rpc_transport );

		return $client->call( $address, ( new OracleAbi() )->get(), $method );
	}
}
