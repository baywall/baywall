<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Cookie;

use Baywall\Core\Application\Port\RefreshTokenCookieProvider;
use Baywall\Core\Application\Port\Clock;
use Baywall\Core\Infrastructure\WordPress\Constants\WpConfig;
use Baywall\Core\Domain\Entity\RefreshToken;
use Baywall\Core\Domain\Service\CookieNameProvider;
use Baywall\Core\Application\ValueObject\Cookie;
use Baywall\Core\Infrastructure\WordPress\Service\WordPressPropertyProvider;

class WpRefreshTokenCookieProvider implements RefreshTokenCookieProvider {
	public function __construct(
		private readonly WordPressPropertyProvider $wordpress_property_provider,
		private readonly CookieNameProvider $cookie_name_provider,
		private readonly Clock $clock
	) {}

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
		$api_root_url = $this->wordpress_property_provider->apiRootUrl();
		return parse_url( trailingslashit( $api_root_url ) . WpConfig::REST_NAMESPACE . '/' . WpConfig::REST_ROUTE_AUTH_PREFIX, PHP_URL_PATH );
	}

	private function secure(): bool {
		// ローカル環境のみ、HTTPSでなくてもクッキーを送信する
		// ※ HTTP環境のWordPressでは本プラグインは動作しないため、インストール時にチェックが必要
		return $this->wordpress_property_provider->getEnvironmentType() === 'local' ? $this->wordpress_property_provider->isSsl() : true;
	}

	/**
	 * リフレッシュトークンを無効化するための期限切れCookieを返します。
	 *
	 * name, path, domain, secure, httponly, samesite が get() と完全に一致し、expires が過去の Cookie を返します。
	 */
	public function getExpired(): Cookie {
		return Cookie::create(
			$this->cookie_name_provider->refreshToken(), // name: get()と同一
			'', // value: 空文字
			$this->clock->now()->value() - 3600, // expires: 過去（1時間前）
			$this->path(),
			null, // domain: get()と同一
			$this->secure(),
			true, // httponly: get()と同一
			'Strict' // samesite: get()と同一
		);
	}
}
