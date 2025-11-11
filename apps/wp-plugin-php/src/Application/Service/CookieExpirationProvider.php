<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Constant\Config;

// ブラウザに書き込むCookieの有効期限を取得するクラス
class CookieExpirationProvider {

	/** Cookieに書き込むアクセストークンの有効期限(秒)を取得します */
	public function accessToken(): int {
		return Config::ACCESS_TOKEN_EXPIRATION;
	}

	/** Cookieに書き込むリフレッシュトークンの有効期限(秒)を取得します */
	public function refreshToken(): int {
		return Config::REFRESH_TOKEN_EXPIRATION;
	}
}
