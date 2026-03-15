<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Constant;

/**
 * WordPressにのみ関連する設定値を取得するためのクラス
 */
class WpConfig {

	/**
	 * このプラグインのルートディレクトリ
	 * (エントリファイルが存在するディレクトリのパス)
	 */
	public const ROOT_DIR = __DIR__ . '/../..';

	/**
	 * ペイウォールブロックのブロック名
	 * ※ block.jsonの"name"フィールドと整合性を取ること
	 */
	public const PAYWALL_BLOCK_NAME = 'baywall/paywall';

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

	/** REST APIの名前空間 */
	// プラグイン名を小文字にしたものを識別子として使用
	public const REST_NAMESPACE = 'baywall';

	/** GraphQLのルート名 */
	public const GRAPHQL_ROUTE = 'graphql';

	/** アクセストークンの有効期限(秒) */
	public const ACCESS_TOKEN_EXPIRATION = 60 * 15; // 15分
	/** リフレッシュトークンの有効期限(秒) */
	public const REFRESH_TOKEN_EXPIRATION_DURATION = 60 * 60 * 24 * 14; // 2週間
	/** 請求書トークンの有効期限(秒) */
	public const INVOICE_TOKEN_EXPIRATION_DURATION = 60 * 15; // 15分

	/** JWTの署名アルゴリズム */
	public const JWT_ALGORITHM = 'HS256'; // HMAC-SHA256
	/** JWTの秘密鍵の長さ(文字数) */
	public const JWT_SECRET_KEY_LENGTH = 64;

	/** アクセストークン(+リフレッシュトークン)更新のルート名 */
	public const REST_ROUTE_AUTH_REFRESH = 'auth/token/refresh';
	/** 請求書トークンをアクセストークン(+リフレッシュトークン)と引き換えるルート名 */
	public const REST_ROUTE_AUTH_TOKEN_INVOICE = 'auth/token/invoice';
	/** 有料コンテンツを取得するルート名 */
	public const REST_ROUTE_PAID_CONTENT = 'paid-content';

	/** リフレッシュトークンを保存するクッキー名 */
	public const COOKIE_NAME_REFRESH_TOKEN = self::REST_NAMESPACE . '_refresh_token';
	/** アクセストークンを保存するクッキー名 */
	public const COOKIE_NAME_ACCESS_TOKEN = self::REST_NAMESPACE . '_access_token';
	/** 請求書トークンを保存するクッキー名 */
	public const COOKIE_NAME_INVOICE_TOKEN = self::REST_NAMESPACE . '_invoice_token';

	/** Gutenbergブロックで設定された販売ネットワークカテゴリIDの属性名 */
	public const BLOCK_ATTR_NAME_SELLING_NETWORK_CATEGORY_ID = 'sellingNetworkCategoryId';
	/** Gutenbergブロックで設定された販売価格の金額の属性名 */
	public const BLOCK_ATTR_NAME_SELLING_AMOUNT = 'sellingAmount';
	/** Gutenbergブロックで設定された販売価格の通貨の属性名 */
	public const BLOCK_ATTR_NAME_SELLING_SYMBOL = 'sellingSymbol';

	private const BLOCK_BUILD_RELATIVE_DIR = 'public/block';
	/** `block.json`へのパス */
	public const BLOCK_JSON_PATH = self::ROOT_DIR . '/' . self::BLOCK_BUILD_RELATIVE_DIR . '/block.json';
	/** ブロックエディタ用の『index.asset.php』ファイルへのパス */
	public const BLOCK_ASSET_PATH = self::ROOT_DIR . '/' . self::BLOCK_BUILD_RELATIVE_DIR . '/index.asset.php';
	/** ブロックエディタ用の『index.js』ファイルへの相対パス(URL生成に使用) */
	public const BLOCK_JS_RELATIVE_PATH = self::BLOCK_BUILD_RELATIVE_DIR . '/index.js';
	/** ブロックエディタ用の『index.css』ファイルへの相対パス(URL生成に使用) */
	public const BLOCK_CSS_RELATIVE_PATH = self::BLOCK_BUILD_RELATIVE_DIR . '/index.css';
	/** ペイウォールブロックスクリプトのハンドル名 */
	public const HANDLE_NAME_BLOCK_SCRIPT = '6e7ba80738b3f81da8c4f83d13e6a344'; // 『src/block/index.js』(文字列)のMD5ハッシュ値

	private const ADMIN_BUILD_RELATIVE_DIR = 'public/admin';
	/** 管理画面用『index.asset.php』ファイルへのパス */
	public const ADMIN_ASSET_PATH = self::ROOT_DIR . '/' . self::ADMIN_BUILD_RELATIVE_DIR . '/index.asset.php';
	/** 管理画面用『index.js』ファイルへの相対パス(URL生成に使用) */
	public const ADMIN_JS_RELATIVE_PATH = self::ADMIN_BUILD_RELATIVE_DIR . '/index.js';
	/** 管理画面スクリプトのハンドル名 */
	public const HANDLE_NAME_ADMIN_SCRIPT = '4c452b4ecb0e32a9563a7a76a9d5ee2c'; // 『public/admin/index.js』(文字列)のMD5ハッシュ値

	private const VIEW_BUILD_RELATIVE_DIR = 'public/view';
	/** ゲストユーザー(一般の訪問者)表示用『index.asset.php』ファイルへのパス */
	public const VIEW_ASSET_PATH = self::ROOT_DIR . '/' . self::VIEW_BUILD_RELATIVE_DIR . '/index.asset.php';
	/** ゲストユーザー(一般の訪問者)表示用『index.js』ファイルへの相対パス(URL生成に使用) */
	public const VIEW_JS_RELATIVE_PATH = self::VIEW_BUILD_RELATIVE_DIR . '/index.js';
	/** ゲストユーザー(一般の訪問者)表示用『index.css』ファイルへの相対パス(URL生成に使用) */
	public const VIEW_CSS_RELATIVE_PATH = self::VIEW_BUILD_RELATIVE_DIR . '/index.css';
	/** 投稿表示用スクリプトのハンドル名 */
	public const HANDLE_NAME_VIEW_SCRIPT = '7f21752c82485b2bc9afb940ba2a6794'; // 『public/view/index.js』(文字列)のMD5ハッシュ値
}
