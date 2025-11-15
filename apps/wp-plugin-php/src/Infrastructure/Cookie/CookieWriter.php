<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Cookie;

class CookieWriter {
	public function set( Cookie $cookie ): bool {
		return setcookie( $cookie->name(), $cookie->value(), $cookie->options() );
	}
}
