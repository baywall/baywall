<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3\Service;

use Cornix\Serendipity\Core\Domain\Exception\RateNotFoundException;
use Cornix\Serendipity\Core\Domain\Service\RateProvider;
use Cornix\Serendipity\Core\Domain\ValueObject\SymbolPair;
use Cornix\Serendipity\Core\Domain\ValueObject\Rate;
use Cornix\Serendipity\Core\Infrastructure\Cache\OracleRateCache;

class CachedOracleRateProvider implements RateProvider {
	public function __construct( OracleRateProvider $oracle_rate_provider, OracleResolver $oracle_resolver, OracleRateCache $oracle_rate_cache ) {
		$this->oracle_rate_provider = $oracle_rate_provider;
		$this->oracle_resolver      = $oracle_resolver;
		$this->oracle_rate_cache    = $oracle_rate_cache;
	}
	private OracleRateProvider $oracle_rate_provider;
	private OracleResolver $oracle_resolver;
	private OracleRateCache $oracle_rate_cache;

	/** @inheritdoc */
	public function getRate( SymbolPair $symbol_pair ): Rate {
		$oracle = $this->oracle_resolver->resolveRateOracle( $symbol_pair );
		if ( $oracle === null ) {
			// オラクルが見つからない場合は例外を投げる
			throw new RateNotFoundException( "[2C1C6F2A] No available oracle for symbol pair: {$symbol_pair->base()}-{$symbol_pair->quote()}" );
		}

		// キャッシュから取得
		$rate = $this->oracle_rate_cache->get( $oracle );

		// キャッシュにない場合は、元のレートプロバイダから取得して保存
		if ( $rate === null ) {
			$rate = $this->oracle_rate_provider->getRate( $symbol_pair );
			$this->oracle_rate_cache->set( $oracle, $rate );
		}

		return $rate;
	}

	/** @inheritdoc */
	public function supports( SymbolPair $symbol_pair ): bool {
		return $this->oracle_rate_provider->supports( $symbol_pair );
	}
}
