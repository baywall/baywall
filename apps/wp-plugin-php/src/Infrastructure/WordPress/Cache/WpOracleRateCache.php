<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Cache;

use Cornix\Serendipity\Core\Domain\Entity\Oracle;
use Cornix\Serendipity\Core\Domain\ValueObject\Amount;
use Cornix\Serendipity\Core\Domain\ValueObject\Rate;
use Cornix\Serendipity\Core\Infrastructure\Cache\OracleRateCache;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\PrefixProvider;

class WpOracleRateCache implements OracleRateCache {

	public function __construct( TransientStorage $storage, PrefixProvider $prefix_provider, TransientExpirationProvider $expiration_provider ) {
		$this->storage    = $storage;
		$this->prefix     = $prefix_provider->transientKey();
		$this->expiration = $expiration_provider->rate();
	}

	private TransientStorage $storage;
	private string $prefix;
	private int $expiration;

	public function set( Oracle $oracle, Rate $rate ): void {
		$transient_key = $this->transientKey( $oracle );

		// レートの値を保存
		$this->storage->set( $transient_key, $rate->amount()->value(), $this->expiration );
	}

	public function get( Oracle $oracle ): ?Rate {
		$transient_key = $this->transientKey( $oracle );
		$result        = $this->storage->get( $transient_key );

		// レートが取得できた場合はRateインスタンスを返す
		return $result === null ? null : Rate::from( $oracle->symbolPair(), Amount::from( $result ) );
	}

	private function transientKey( Oracle $oracle ): string {
		// OracleのチェーンID＋アドレスでキーとしては一意になるが、
		// データを見たときに認識しやすくするため、symbolを含める
		$chain_id_value       = $oracle->chainId()->value();
		$oracle_address_value = $oracle->address()->value();
		$base_value           = $oracle->symbolPair()->base()->value();
		$quote_value          = $oracle->symbolPair()->quote()->value();

		return "{$this->prefix}oracle_rate_{$chain_id_value}_{$base_value}_{$quote_value}_{$oracle_address_value}";
	}
}
