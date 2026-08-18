<?php
declare(strict_types=1);
namespace Baywall\Core\Presentation\Hooks\Service;

use Baywall\Core\Infrastructure\WordPress\Constants\WpConfig;
use Baywall\Core\Infrastructure\WordPress\Service\WordPressPropertyProvider;

class PhpVarExporter {

	/**
	 * @param string      $handle       インラインスクリプトを追加するスクリプトハンドル名
	 * @param string|null $view_css_url Shadow DOM 内に注入するウィジェットCSSのURL(バージョンクエリ付き)。
	 *                                  指定した場合は出力JSONに`viewCssUrl`キーとして追加する。
	 *                                  管理画面・ブロックエディタ等では不要なため省略可能(後方互換)。
	 */
	public function addInlineScript( string $handle, ?string $view_css_url = null ): void {
		// javascriptとして出力する際の変数名を取得
		$js_var_name = WpConfig::PHP_VAR_NAME;

		$success = wp_add_inline_script(
			$handle,
			"var {$js_var_name} = " . wp_json_encode( $this->getPhpVar( $view_css_url ) ) . ';',
			'before',   // スクリプトの前に追加
		);

		assert( $success );
	}

	/**
	 * `wp_add_inline_script`で出力する値を返します
	 *
	 * @param string|null $view_css_url ウィジェットCSSのURL(指定時のみ`viewCssUrl`キーに含める)
	 */
	private function getPhpVar( ?string $view_css_url = null ): array {
		// REST APIアクセス用のnonce
		$wp_rest_nonce = wp_create_nonce( 'wp_rest' );

		// GraphQL APIのURL
		$wp_property = new WordPressPropertyProvider();

		// 出力する変数
		$result = array(
			'wpRestNonce' => $wp_rest_nonce,
			'apiRoot'     => trailingslashit( $wp_property->apiRootUrl() ),
		);

		// 現在の投稿IDが取得できる場合は追加(取得できなかった場合はnull)
		$post_id          = get_the_ID();
		$result['postId'] = false === $post_id ? null : $post_id;

		// ウィジェットCSSのURLが指定された場合は追加(Shadow DOM内に<link>で注入するために使用)
		if ( null !== $view_css_url ) {
			$result['viewCssUrl'] = $view_css_url;
		}

		return $result;
	}
}
