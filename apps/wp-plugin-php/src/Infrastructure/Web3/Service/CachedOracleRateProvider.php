<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3\Service;

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

	public function getRate( SymbolPair $symbol_pair ): ?Rate {
		$oracle = $this->oracle_resolver->resolveRateOracle( $symbol_pair );
		if ( $oracle === null ) {
			// TODO: RateNotFoundException を投げるようにする #300
			return null; // 利用可能なオラクルがない場合はnullを返す
		}

		// キャッシュから取得
		$cached_rate = $this->oracle_rate_cache->get( $oracle );
		if ( $cached_rate !== null ) {
			return $cached_rate;
		}

		// キャッシュにない場合は、元のレートプロバイダから取得
		$rate = $this->oracle_rate_provider->getRate( $symbol_pair );

		// キャッシュに保存してから返す
		$this->oracle_rate_cache->set( $oracle, $rate );
		return $rate;
	}
}
