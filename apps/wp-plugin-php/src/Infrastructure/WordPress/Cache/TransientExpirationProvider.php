<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Cache;

use Cornix\Serendipity\Core\Constant\Config;

class TransientExpirationProvider {
	public function rate(): int {
		return Config::RATE_TRANSIENT_EXPIRATION;
	}
}
