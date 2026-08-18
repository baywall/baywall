<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Cache;

use Baywall\Core\Constant\Config;

class TransientExpirationProvider {
	public function rate(): int {
		return Config::RATE_TRANSIENT_EXPIRATION;
	}
}
