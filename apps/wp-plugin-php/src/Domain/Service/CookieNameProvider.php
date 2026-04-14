<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Service;

interface CookieNameProvider {
	/** アクセストークンのCookie名を取得します。 */
	public function accessToken(): string;

	/** リフレッシュトークンのCookie名を取得します。 */
	public function refreshToken(): string;

	/** 請求書トークンのCookie名を取得します。 */
	public function invoiceToken(): string;
}
