<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Cache;

use Cornix\Serendipity\Core\Domain\ValueObject\Amount;
use Cornix\Serendipity\Core\Domain\ValueObject\Rate;
use Cornix\Serendipity\Core\Domain\ValueObject\SymbolPair;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\PrefixProvider;

class RateTransient {

	public function __construct( PrefixProvider $prefix_provider, TransientExpirationProvider $expiration_provider ) {
		$this->prefix     = $prefix_provider->transientKey();
		$this->expiration = $expiration_provider->rate();
	}

	private string $prefix;
	private int $expiration;

	public function set( Rate $rate ): void {
		$transient_key = $this->transientKey( $rate->symbolPair() );
		$rate_value    = $rate->amount()->value();

		// レートの数値部分をvalueとして保存
		$success = set_transient( $transient_key, $rate_value, $this->expiration );
		if ( true !== $success ) {
			throw new \RuntimeException( "[08CC23A4] Failed to set transient for key: {$transient_key}" );
		}
	}

	public function get( SymbolPair $symbol_pair ): ?Rate {
		$transient_key = $this->transientKey( $symbol_pair );
		$result        = get_transient( $transient_key );

		return false === $result ? null : Rate::from( $symbol_pair, Amount::from( $result ) );
	}

	private function transientKey( SymbolPair $symbol_pair ): string {
		$base  = $symbol_pair->base();
		$quote = $symbol_pair->quote();
		return "{$this->prefix}{$base}_{$quote}";
	}
}
