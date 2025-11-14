<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Application\Service\RefreshTokenCookieProvider;
use Cornix\Serendipity\Core\Constant\WpConfig;
use Cornix\Serendipity\Core\Domain\Entity\RefreshToken;
use Cornix\Serendipity\Core\Infrastructure\Cookie\Cookie;
use Cornix\Serendipity\Core\Infrastructure\Util\Strings;

class WpRefreshTokenCookieProvider implements RefreshTokenCookieProvider {

	private WordPressPropertyProvider $wp_property;

	public function __construct( WordPressPropertyProvider $wp_property ) {
		$this->wp_property = $wp_property;
	}

	public function get( RefreshToken $refresh_token ): Cookie {
		return Cookie::create(
			WpConfig::COOKIE_NAME_REFRESH_TOKEN, // name
			$refresh_token->token()->value(), // value
			$refresh_token->expiresAt()->value(), // expires
			$this->path(),
			null, // domain: nullで発行元ホスト名が自動設定される
			$this->secure(),
			true, // httponly: trueに設定してJSからのアクセスを防止
			'Strict' // samesite
		);
	}

	private function expires(): int {
		return time() + WpConfig::REFRESH_TOKEN_EXPIRATION_DURATION;
	}

	/** Cookieに書き込むリフレッシュトークンのパスを取得します */
	private function path(): string {
		$api_root_url = $this->wp_property->apiRootUrl();
		if ( Strings::contains( $api_root_url, '?' ) ) {
			assert( Strings::contains( $api_root_url, '/index.php?rest_route=' ), "[E58E182F] {$api_root_url}" );
			// パーマリンクがデフォルトのままの場合、pathはルートを指定
			// ※ セキュリティが低くなるのでダッシュボード等で警告を表示
			$path = '/';
		} else {
			assert( Strings::contains( $api_root_url, '/wp-json/' ), "[25ECA6F7] api_root_url: {$api_root_url}" );
			$path = parse_url( $api_root_url, PHP_URL_PATH ) . WpConfig::REST_NAMESPACE . '/' . WpConfig::REST_ROUTE_AUTH_REFRESH;
			assert( ! Strings::contains( $path, '//' ), "[FAB98DDF] {$api_root_url}" );
		}
		return $path;
	}

	private function secure(): bool {
		// ローカル環境のみ、HTTPSでなくてもクッキーを送信する
		// ※ HTTP環境のWordPressでは本プラグインは動作しないため、インストール時にチェックが必要
		return $this->wp_property->getEnvironmentType() === 'local' ? $this->wp_property->isSsl() : true;
	}
}
