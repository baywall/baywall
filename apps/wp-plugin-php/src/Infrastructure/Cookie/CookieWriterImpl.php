<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Cookie;

use Baywall\Core\Application\Port\CookieWriter;
use Baywall\Core\Application\ValueObject\Cookie;

class CookieWriterImpl implements CookieWriter {
	public function set( Cookie $cookie ): bool {
		return setcookie( $cookie->name(), $cookie->value(), $cookie->options() );
	}
}
