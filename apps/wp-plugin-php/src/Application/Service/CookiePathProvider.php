<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

interface CookiePathProvider {
	/** Cookieに書き込むリフレッシュトークンのパスを取得します */
	public function refreshToken(): string;
}
