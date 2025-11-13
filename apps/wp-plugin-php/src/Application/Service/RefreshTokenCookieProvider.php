<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Infrastructure\Cookie\Cookie;
use Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject\RefreshToken;

/**
 * リフレッシュトークンをCookieに書き込む際のプロパティを提供します
 */
interface RefreshTokenCookieProvider {
	public function get( RefreshToken $refresh_token ): Cookie;
}
