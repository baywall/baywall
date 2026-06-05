<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Domain\Entity\RefreshToken;
use Cornix\Serendipity\Core\Infrastructure\Cookie\Cookie;

/**
 * リフレッシュトークンをCookieに書き込む際のプロパティを提供します
 */
interface RefreshTokenCookieProvider {
	public function get( RefreshToken $refresh_token ): Cookie;

	/**
	 * リフレッシュトークンを無効化するための期限切れCookieを返します。
	 *
	 * 同名・同path・同domain・同secure・同httponly・同samesite で expires=過去 の Cookie を返します。
	 */
	public function getExpired(): Cookie;
}
