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
		$auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null;
		// $_SERVERから取得できなかった場合は`getallheaders()`を使用
		if ( $auth_header === null && function_exists( 'getallheaders' ) ) {
			$headers     = getallheaders();
			$auth_header = $headers['Authorization'] ?? $headers['authorization'] ?? null;
		}

		if ( $auth_header !== null ) {
			if ( preg_match( '/Bearer\s(\S+)/', $auth_header, $matches ) ) {
				return $matches[1];
			}
		}

		return null;
	}
}
