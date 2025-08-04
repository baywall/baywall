<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Domain\Service\RateProvider;
use Cornix\Serendipity\Core\Domain\ValueObject\SymbolPair;
use Cornix\Serendipity\Core\Domain\ValueObject\Rate;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Cache\RateTransient;

class CachedRateProvider implements RateProvider {

	public function __construct( RateTransient $rate_transient, RateProvider $rate_provider ) {
		$this->rate_transient = $rate_transient;
		$this->rate_provider  = $rate_provider;
	}

	private RateTransient $rate_transient;
	private RateProvider $rate_provider;

	public function getRate( SymbolPair $symbol_pair ): ?Rate {
		// キャッシュから取得
		$cached_rate = $this->rate_transient->get( $symbol_pair );
		if ( $cached_rate !== null ) {
			return $cached_rate;
		}

		// キャッシュにない場合は、元のレートプロバイダから取得
		$rate = $this->rate_provider->getRate( $symbol_pair );
		if ( $rate !== null ) {
			// 取得したレートをキャッシュに保存
			$this->rate_transient->set( $rate );
		}
		return $rate;
	}
}
