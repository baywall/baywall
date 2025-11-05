<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Presentation\Hooks\Service;

use Cornix\Serendipity\Core\Constant\WpConfig;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WordPressPropertyProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\RestPropertyProvider;

class PhpVarExporter {

	/**
	 * @param string $handle インラインスクリプトを追加するスクリプトハンドル名
	 */
	public function addInlineScript( string $handle ): void {
		// javascriptとして出力する際の変数名を取得
		$js_var_name = WpConfig::PHP_VAR_NAME;

		$success = wp_add_inline_script(
			$handle,
			"var {$js_var_name} = " . wp_json_encode( $this->getPhpVar() ) . ';',
			'before',   // スクリプトの前に追加
		);

		assert( $success );
	}

	/** `wp_add_inline_script`で出力する値を返します */
	private function getPhpVar(): array {
		// REST APIアクセス用のnonce
		$wp_rest_nonce = wp_create_nonce( 'wp_rest' );

		// GraphQL APIのURL
		$wp_property   = new WordPressPropertyProvider();
		$rest_property = new RestPropertyProvider();
		$graphql_url   = untrailingslashit( $wp_property->apiRootUrl() ) . '/' . $rest_property->namespace() . '/' . $rest_property->graphQlRoute();

		// 出力する変数
		$result = array(
			'wpRestNonce' => $wp_rest_nonce,
			'graphqlUrl'  => $graphql_url,
		);

		// 現在の投稿IDが取得できる場合は追加(取得できなかった場合はnull)
		$post_id          = get_the_ID();
		$result['postId'] = false === $post_id ? null : $post_id;

		return $result;
	}
}
