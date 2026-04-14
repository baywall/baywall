<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Application\Service\AccessTokenCookieProvider;
use Cornix\Serendipity\Core\Application\ValueObject\AccessToken;
use Cornix\Serendipity\Core\Domain\Service\CookieNameProvider;
use Cornix\Serendipity\Core\Constant\WpConfig;
use Cornix\Serendipity\Core\Infrastructure\Cookie\Cookie;

class WpAccessTokenCookieProvider implements AccessTokenCookieProvider {

	private WordPressPropertyProvider $wp_property;
	private CookieNameProvider $cookie_name_provider;

	public function __construct( WordPressPropertyProvider $wp_property, CookieNameProvider $cookie_name_provider ) {
		$this->wp_property          = $wp_property;
		$this->cookie_name_provider = $cookie_name_provider;
	}

	public function get( AccessToken $access_token ): Cookie {
		return Cookie::create(
			$this->cookie_name_provider->accessToken(), // name
			$access_token->value(), // value
			time() + WpConfig::ACCESS_TOKEN_EXPIRATION, // expires
			$this->path(),
			null, // domain: nullで発行元ホスト名が自動設定される
			$this->secure(),
			true, // httponly: trueに設定してJSからのアクセスを防止
			'Strict' // samesite
		);
	}

	/**
	 * Cookieに書き込むアクセストークンのパスを取得します。
	 *
	 * Prettyパーマリンク時は paid-content API に限定した path を設定し、
	 * Plainパーマリンク時はWordPress仕様により /index.php になる。
	 */
	private function path(): string {
		$api_root_url = $this->wp_property->apiRootUrl();
		return parse_url( trailingslashit( $api_root_url ) . WpConfig::REST_NAMESPACE . '/' . WpConfig::REST_ROUTE_PAID_CONTENT, PHP_URL_PATH );
	}

	private function secure(): bool {
		// ローカル環境のみ、HTTPSでなくてもクッキーを送信する
		// ※ HTTP環境のWordPressでは本プラグインは動作しないため、インストール時にチェックが必要
		return $this->wp_property->getEnvironmentType() === 'local' ? $this->wp_property->isSsl() : true;
	}
}
