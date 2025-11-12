<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Constant;

/**
 * WordPressにのみ関連する設定値を取得するためのクラス
 */
class WpConfig {

	// private const PLUGIN_NAME = 'baywall';

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
	 * REST APIの名前空間
	 *
	 * プラグイン名を小文字にしたものを識別子として使用
	 */
	public const REST_NAMESPACE = 'baywall';

	/**
	 * GraphQLのルート名
	 */
	public const GRAPHQL_ROUTE = 'graphql';

	/** アクセストークンの有効期限(秒) */
	public const ACCESS_TOKEN_EXPIRATION = 60 * 15; // 15分
	/** リフレッシュトークンの有効期限(秒) */
	public const REFRESH_TOKEN_EXPIRATION = 60 * 60 * 24 * 14; // 2週間

	/** JWTの署名アルゴリズム */
	public const JWT_ALGORITHM = 'HS256'; // HMAC-SHA256
	/** JWTの秘密鍵の長さ(文字数) */
	public const JWT_SECRET_KEY_LENGTH = 64;

	/** アクセストークン(+リフレッシュトークン)更新のルート名 */
	public const REST_ROUTE_AUTH_REFRESH = 'auth/refresh';

	/** リフレッシュトークンを保存するクッキー名 */
	public const COOKIE_NAME_REFRESH_TOKEN = self::REST_NAMESPACE . '_refresh_token';


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
