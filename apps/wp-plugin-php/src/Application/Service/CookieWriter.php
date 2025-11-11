<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

class CookieWriter {
	public function set( string $name, string $value, array $options ): bool {
		return setcookie( $name, $value, $options );
	}
}
