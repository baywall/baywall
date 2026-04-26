<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Application\Service\RefreshTokenCookieProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Constants\WpConfig;
use Cornix\Serendipity\Core\Domain\Entity\RefreshToken;
use Cornix\Serendipity\Core\Domain\Service\CookieNameProvider;
use Cornix\Serendipity\Core\Infrastructure\Cookie\Cookie;

class WpRefreshTokenCookieProvider implements RefreshTokenCookieProvider {

	private WordPressPropertyProvider $wp_property;
	private CookieNameProvider $cookie_name_provider;

	public function __construct( WordPressPropertyProvider $wp_property, CookieNameProvider $cookie_name_provider ) {
		$this->wp_property          = $wp_property;
		$this->cookie_name_provider = $cookie_name_provider;
	}

	public function get( RefreshToken $refresh_token ): Cookie {
		return Cookie::create(
			$this->cookie_name_provider->refreshToken(), // name
			$refresh_token->token()->value(), // value
			$refresh_token->expiresAt()->value(), // expires
			$this->path(),
			null, // domain: nullで発行元ホスト名が自動設定される
			$this->secure(),
			true, // httponly: trueに設定してJSからのアクセスを防止
			'Strict' // samesite
		);
	}

	/** Cookieに書き込むリフレッシュトークンのパスを取得します */
	private function path(): string {
		$api_root_url = $this->wp_property->apiRootUrl();
		return parse_url( trailingslashit( $api_root_url ) . WpConfig::REST_NAMESPACE . '/' . WpConfig::REST_ROUTE_AUTH_REFRESH, PHP_URL_PATH );
	}

	private function secure(): bool {
		// ローカル環境のみ、HTTPSでなくてもクッキーを送信する
		// ※ HTTP環境のWordPressでは本プラグインは動作しないため、インストール時にチェックが必要
		return $this->wp_property->getEnvironmentType() === 'local' ? $this->wp_property->isSsl() : true;
	}
}
