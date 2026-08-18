<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Web3\Service;

use Baywall\Core\Domain\Exception\RateNotFoundException;
use Baywall\Core\Domain\Service\RateProvider;
use Baywall\Core\Domain\ValueObject\Amount;
use Baywall\Core\Domain\ValueObject\SymbolPair;
use Baywall\Core\Domain\ValueObject\Rate;
use Baywall\Core\Infrastructure\Web3\Factory\OracleClientFactory;

class OracleRateProvider implements RateProvider {
	public function __construct( OracleClientFactory $oracle_client_factory, OracleResolver $oracle_resolver ) {
		$this->oracle_client_factory = $oracle_client_factory;
		$this->oracle_resolver       = $oracle_resolver;
	}
	private OracleClientFactory $oracle_client_factory;
	private OracleResolver $oracle_resolver;

	/** @inheritdoc */
	public function getRate( SymbolPair $symbol_pair ): Rate {
		// 接続可能なオラクルを取得
		$oracle = $this->oracle_resolver->resolveRateOracle( $symbol_pair );
		if ( $oracle === null ) {
			// オラクルが見つからない場合は例外を投げる
			throw new RateNotFoundException( "[C0A31594] No available oracle for symbol pair: {$symbol_pair->base()}-{$symbol_pair->quote()}" );
		}

		// オラクルへ接続するインスタンスを作成
		$oracle_client = $this->oracle_client_factory->create( $oracle );

		// オラクルから小数点以下桁数とレートを取得
		$decimals = $oracle_client->decimals();
		$answer   = $oracle_client->latestAnswer();

		$rate_amount = Amount::fromBaseUnitAndDecimals( $answer->toString(), $decimals );
		return Rate::from( $symbol_pair, $rate_amount );
	}

	/** @inheritdoc */
	public function supports( SymbolPair $symbol_pair ): bool {
		// 接続可能なオラクルを取得
		$oracle = $this->oracle_resolver->resolveRateOracle( $symbol_pair );
		return $oracle !== null;
	}
}
