<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Service;

use Baywall\Core\Application\ValueObject\Cookie;

/**
 * Cookieへ書き込む手段を提供します。
 */
interface CookieWriter {
	public function set( Cookie $cookie ): bool;
}
