<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Infrastructure\Http;

class BearerTokenService {

	/**
	 * AuthorizationヘッダからBearerトークンを取得します
	 *
	 * ※ PHP-FPM+Nginxのような環境の場合、Authorizationヘッダが$_SERVERにセットされないことがあるため、
	 *    そのような場合はNginxの設定を変更する必要があります。
	 *    例: fastcgi_param HTTP_AUTHORIZATION $http_authorization;
	 */
	public function get(): ?string {

		// $_SERVER から取得
		$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null;
		// $_SERVERから取得できなかった場合は`getallheaders()`を使用
		if ( $authHeader === null && function_exists( 'getallheaders' ) ) {
			$headers    = getallheaders();
			$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
		}

		if ( $authHeader !== null ) {
			if ( preg_match( '/Bearer\s(\S+)/', $authHeader, $matches ) ) {
				return $matches[1];
			}
		}

		return null;
	}
}
