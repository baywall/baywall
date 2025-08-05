<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3\Service;

use Cornix\Serendipity\Core\Domain\Service\RateProvider;
use Cornix\Serendipity\Core\Domain\ValueObject\Amount;
use Cornix\Serendipity\Core\Domain\ValueObject\SymbolPair;
use Cornix\Serendipity\Core\Domain\ValueObject\Rate;
use Cornix\Serendipity\Core\Infrastructure\Web3\Factory\OracleClientFactory;

class OracleRateProvider implements RateProvider {
	public function __construct( OracleClientFactory $oracle_client_factory, OracleResolver $oracle_resolver ) {
		$this->oracle_client_factory = $oracle_client_factory;
		$this->oracle_resolver       = $oracle_resolver;
	}
	private OracleClientFactory $oracle_client_factory;
	private OracleResolver $oracle_resolver;

	public function getRate( SymbolPair $symbol_pair ): ?Rate {
		// 接続可能なオラクルを取得
		$oracle = $this->oracle_resolver->resolveRateOracle( $symbol_pair );
		if ( null === $oracle ) {
			// TODO: RateNotFoundException を投げるようにする #300
			return null; // 利用可能なオラクルがない場合はnullを返す
		}

		// オラクルへ接続するインスタンスを作成
		$oracle_client = $this->oracle_client_factory->create( $oracle );

		// オラクルから小数点以下桁数とレートを取得
		$decimals = $oracle_client->decimals();
		$answer   = $oracle_client->latestAnswer();

		$rate_amount = Amount::fromBaseUnitAndDecimals( $answer->toString(), $decimals );
		return Rate::from( $symbol_pair, $rate_amount );
	}
}
