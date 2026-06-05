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

	/**
	 * アクセストークンを無効化するための期限切れCookieを返します。
	 *
	 * 同名・同path・同domain・同secure・同httponly・同samesite で expires=過去 の Cookie を返します。
	 */
	public function getExpired(): Cookie;
}
