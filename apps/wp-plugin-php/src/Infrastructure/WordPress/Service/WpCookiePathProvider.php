<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Application\Service\CookiePathProvider;
use Cornix\Serendipity\Core\Constant\WpConfig;
use Cornix\Serendipity\Core\Infrastructure\Util\Strings;

// ブラウザに書き込むCookieのパス(setcookieでpathに指定する値)を取得するクラス
class WpCookiePathProvider implements CookiePathProvider {

	private WordPressPropertyProvider $wp_property;

	public function __construct( WordPressPropertyProvider $wp_property ) {
		$this->wp_property = $wp_property;
	}

	/** Cookieに書き込むアクセストークンのパスを取得します */
	public function accessToken(): string {
		// アクセストークンはサイト全体で利用するため、ルートパスを返す
		return '/';
	}

	/** Cookieに書き込むリフレッシュトークンのパスを取得します */
	public function refreshToken(): string {
		$api_root_url = $this->wp_property->apiRootUrl();
		if ( Strings::contains( $api_root_url, '?' ) ) {
			assert( Strings::contains( $api_root_url, '/index.php?rest_route=' ) );
			// パーマリンクがデフォルトのままの場合、pathはルートを指定
			// ※ セキュリティが低くなるのでダッシュボード等で警告を表示
			$path = '/';
		} else {
			assert( Strings::contains( $api_root_url, '/wp-json/' ) );
			$path = parse_url( $api_root_url, PHP_URL_PATH ) . WpConfig::REST_NAMESPACE . '/' . WpConfig::REST_ROUTE_AUTH_REFRESH;
			assert( ! Strings::contains( $path, '//' ) );
		}
		return $path;
	}
}
