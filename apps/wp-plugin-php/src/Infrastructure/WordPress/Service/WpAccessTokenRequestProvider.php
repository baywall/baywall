<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Application\Service\AccessTokenRequestProvider;
use Cornix\Serendipity\Core\Constant\WpConfig;

class WpAccessTokenRequestProvider implements AccessTokenRequestProvider {
	public function get(): ?string {
		/** @var string|null */
		$access_token = $_COOKIE[ WpConfig::COOKIE_NAME_ACCESS_TOKEN ] ?? null;
		return $access_token;
	}
}
