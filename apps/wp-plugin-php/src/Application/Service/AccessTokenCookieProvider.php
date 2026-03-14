<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Application\ValueObject\AccessToken;
use Cornix\Serendipity\Core\Infrastructure\Cookie\Cookie;

/**
 * アクセストークンをCookieに書き込む際のプロパティを提供します
 */
interface AccessTokenCookieProvider {
	public function get( AccessToken $access_token ): Cookie;
}
