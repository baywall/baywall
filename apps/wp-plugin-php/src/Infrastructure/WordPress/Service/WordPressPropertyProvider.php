<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

/**
 * WordPressの設定情報を取得するクラス。
 */
// ※ このクラスはWordPressから情報を取得するためのクラスなので、WpPropertyProviderという名前を避けています。
class WordPressPropertyProvider {

	/**
	 * サイトのホームURLを取得します。
	 *
	 * 「設定 > 一般」の「サイトアドレス (URL)」(サイト訪問者がアクセスするURL)
	 */
	public function homeUrl(): string {
		return get_home_url();
	}

	/** REST APIのルートURLを取得します。 */
	public function apiRootUrl(): string {
		return get_rest_url();

		/*
		get_rest_url() 置き換え前は以下のコードでパーマリンクがデフォルトの場合とそうでない場合を判定してした

		// パーマリンク構造が基本の場合は、`/wp-json/`を含むURLではアクセスできないので`?rest_route=`を含むURLでAPIアクセスを行う。
		// 参考: https://labor.ewigleere.net/2021/11/06/wordpress-restapi-404notfound-permalink-basic/
		// 『get_option( 'permalink_structure' ) === ''』 => 「設定 > パーマリンク設定」で「基本」(英語の場合は「Plain」)のパーマリンクが選択されているかどうか
		$api_root_path = get_option( 'permalink_structure' ) === '' ? '/index.php?rest_route=/' : '/wp-json/';
		// 『get_home_url()』 => 「設定 > 一般」の「サイトアドレス (URL)」(サイト訪問者がアクセスするURL)
		return untrailingslashit( get_home_url() ) . $api_root_path;
		*/
	}

	/** SSLでアクセスされているかどうかを取得します */
	public function isSsl(): bool {
		// ※ リバースプロキシを使って設定が漏れている場合はHTTPSアクセスでもfalseを返す可能性あり
		// @see https://www.en-pc.jp/wordpress_isssl_notworking/
		return is_ssl();
	}

	/** マルチサイト構成になっているかどうかを返します。 */
	public function isMultisite(): bool {
		return is_multisite();
	}

	/**
	 *
	 * @return 'local'|'development'|'staging'|'production'
	 */
	public function getEnvironmentType(): string {
		// `WP_ENVIRONMENT_TYPE`に設定された値(default: 'production')を返す。
		// 以下のいずれかの値を返す
		// - local
		// - development
		// - staging
		// - production
		return wp_get_environment_type();
	}
}
