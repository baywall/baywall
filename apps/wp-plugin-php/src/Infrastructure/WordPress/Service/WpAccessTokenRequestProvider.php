<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Service;

use Baywall\Core\Application\Service\AccessTokenRequestProvider;
use Baywall\Core\Domain\Service\CookieNameProvider;

class WpAccessTokenRequestProvider implements AccessTokenRequestProvider {
	private CookieNameProvider $cookie_name_provider;

	public function __construct( CookieNameProvider $cookie_name_provider ) {
		$this->cookie_name_provider = $cookie_name_provider;
	}

	public function get(): ?string {
		/** @var string|null */
		$access_token = $_COOKIE[ $this->cookie_name_provider->accessToken() ] ?? null;
		return $access_token;
	}
}
