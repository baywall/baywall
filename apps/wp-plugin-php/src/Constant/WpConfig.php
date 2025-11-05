<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Constant;

/**
 * WordPressにのみ関連する設定値を取得するためのクラス
 */
class WpConfig {
	/**
	 * ペイウォールブロックに付与するHTMLのCSSクラス名
	 *
	 * ※ TypeScript側と整合性を取ること
	 */
	public const PAYWALL_BLOCK_CLASS_NAME = 'ae6cefc4-82d4-4220-840b-d74538ea7284';

	/**
	 * PHPから渡される変数名
	 * ※ TypeScript側と整合性を取ること
	 */
	public const PHP_VAR_NAME = 'php_var_20792bdd';

	/**
	 * ペイウォールブロックスクリプトのハンドル名
	 *
	 * 『src/block/index.js』(文字列)のMD5ハッシュ値
	 */
	public const HANDLE_NAME_BLOCK_SCRIPT = '6e7ba80738b3f81da8c4f83d13e6a344';

	/**
	 * 管理画面スクリプトのハンドル名
	 *
	 * 『public/admin/index.js』(文字列)のMD5ハッシュ値
	 */
	public const HANDLE_NAME_ADMIN_SCRIPT = '4c452b4ecb0e32a9563a7a76a9d5ee2c';

	/**
	 * 投稿表示用スクリプトのハンドル名
	 *
	 * 『public/view/index.js』(文字列)のMD5ハッシュ値
	 */
	public const HANDLE_NAME_VIEW_SCRIPT = '7f21752c82485b2bc9afb940ba2a6794';
}
