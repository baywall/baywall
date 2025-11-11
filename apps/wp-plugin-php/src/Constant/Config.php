<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Constant;

/**
 * システム固定の設定値を取得するためのクラス
 */
class Config {

	/**
	 * このプラグインのルートディレクトリ
	 * (エントリファイルが存在するディレクトリのパス)
	 */
	public const ROOT_DIR = __DIR__ . '/../../..';

	private const BLOCK_BUILD_RELATIVE_DIR = 'build/block';
	/** `block.json`へのパス */
	public const BLOCK_JSON_PATH = self::ROOT_DIR . '/' . self::BLOCK_BUILD_RELATIVE_DIR . '/block.json';
	/** ブロックエディタ用の『index.asset.php』ファイルへのパス */
	public const BLOCK_ASSET_PATH = self::ROOT_DIR . '/' . self::BLOCK_BUILD_RELATIVE_DIR . '/index.asset.php';
	/** ブロックエディタ用の『index.js』ファイルへの相対パス(URL生成に使用) */
	public const BLOCK_JS_RELATIVE_PATH = self::BLOCK_BUILD_RELATIVE_DIR . '/index.js';
	/** ブロックエディタ用の『index.css』ファイルへの相対パス(URL生成に使用) */
	public const BLOCK_CSS_RELATIVE_PATH = self::BLOCK_BUILD_RELATIVE_DIR . '/index.css';


	/** Gutenbergブロックで設定された販売ネットワークカテゴリIDの属性名 */
	public const BLOCK_ATTR_NAME_SELLING_NETWORK_CATEGORY_ID = 'sellingNetworkCategoryId';
	/** Gutenbergブロックで設定された販売価格の金額の属性名 */
	public const BLOCK_ATTR_NAME_SELLING_AMOUNT = 'sellingAmount';
	/** Gutenbergブロックで設定された販売価格の通貨の属性名 */
	public const BLOCK_ATTR_NAME_SELLING_SYMBOL = 'sellingSymbol';

	/** GraphQLスキーマファイルへのパス */
	public const GRAPHQL_SCHEMA_PATH = self::ROOT_DIR . '/includes/assets/graphql/schema/schema.graphql';
	/** GraphQLスキーマのキャッシュファイル(PHPファイル)へのパス */
	public const GRAPHQL_CACHE_PATH = self::ROOT_DIR . '/includes/cache/graphql-schema.php';

	private const ADMIN_BUILD_RELATIVE_DIR = 'public/admin';
	// 管理画面用『index.asset.php』ファイルへのパス
	public const ADMIN_ASSET_PATH = self::ROOT_DIR . '/' . self::ADMIN_BUILD_RELATIVE_DIR . '/index.asset.php';
	// 管理画面用『index.js』ファイルへの相対パス(URL生成に使用)
	public const ADMIN_JS_RELATIVE_PATH = self::ADMIN_BUILD_RELATIVE_DIR . '/index.js';

	private const VIEW_BUILD_RELATIVE_DIR = 'public/view';
	// ゲストユーザー(一般の訪問者)表示用『index.asset.php』ファイルへのパス
	public const VIEW_ASSET_PATH = self::ROOT_DIR . '/' . self::VIEW_BUILD_RELATIVE_DIR . '/index.asset.php';
	// ゲストユーザー(一般の訪問者)表示用『index.js』ファイルへの相対パス(URL生成に使用)
	public const VIEW_JS_RELATIVE_PATH = self::VIEW_BUILD_RELATIVE_DIR . '/index.js';
	// ゲストユーザー(一般の訪問者)表示用『index.css』ファイルへの相対パス(URL生成に使用)
	public const VIEW_CSS_RELATIVE_PATH = self::VIEW_BUILD_RELATIVE_DIR . '/index.css';

	/** アクセストークンの有効期限(秒) */
	public const ACCESS_TOKEN_EXPIRATION = 60 * 15; // 15分
	/** リフレッシュトークンの有効期限(秒) */
	public const REFRESH_TOKEN_EXPIRATION = 60 * 60 * 24 * 14; // 2週間

	/**
	 * レートの一時データの有効期限(秒)
	 */
	public const RATE_TRANSIENT_EXPIRATION = 60 * 10; // 10分

	/**
	 * ブロックチェーンへのリクエストのタイムアウト(秒)
	 */
	public const BLOCKCHAIN_REQUEST_TIMEOUT = 10;

	/**
	 * ブロックチェーンへのリクエストのリトライ間隔(ミリ秒)
	 */
	public const BLOCKCHAIN_REQUEST_RETRY_INTERVALS_MS = array( 1000, 2000, 4000 );

	/**
	 * Appコントラクトのクロール処理を行うCronの間隔(秒)
	 */
	public const CRON_INTERVAL_APP_CONTRACT_CRAWL = 60 * 1; // 1分

	/**
	 * 最小のブロック待機数
	 * ブロックにトランザクションが取り込まれた時点で1とカウントする
	 */
	public const MIN_CONFIRMATIONS = 1; // 【変更不可】

	// 以下のスレッドで以下の制限があるとの記述あり
	// https://github.com/bnb-chain/bsc/issues/113
	// - BSC: 5000
	// - Alchemy: 2000 => https://docs.alchemy.com/reference/eth-getlogs
	//
	// QuickNodeの無料プランはPolygonであっても最大5ブロックしか取得できない点に注意(有料プランであれば最大10,000ブロック)
	// -> 10秒に1回以上リクエストしないと取得しきれないため、本アプリにおいては使い物にならない
	// https://www.quicknode.com/docs/polygon/eth_getLogs

	// 一旦、Alchemyの制限に合わせる
	// ブロック生成速度が2s/blockの場合、1時間分程度のログ取得が可能。(Cronのインターバルが1時間であっても1回の取得で完了できる)
	/** `eth_getLogs`呼び出しで取得するブロック数の最大値 */
	public const GET_LOGS_MAX_RANGE = 1999;

	/**
	 * ネイティブトークンのアドレス(便宜的に使用するアドレス)
	 *
	 * @see https://github.com/ethereum/ercs/blob/master/ERCS/erc-7528.md
	 */
	public const NATIVE_TOKEN_ADDRESS = '0xEeeeeEeeeEeEeeEeEeEeeEEEeeeeEeeeeeeeEEeE';

	/**
	 * GraphQLの最大クエリ複雑度
	 *
	 * 動作する最小の値を設定
	 *
	 * @see https://webonyx.github.io/graphql-php/security/#query-complexity-analysis
	 */
	public const GRAPHQL_MAX_COMPLEXITY = 28;

	/**
	 * GraphQLの最大クエリ深度
	 *
	 * 動作する最小の値を設定
	 *
	 * @see https://webonyx.github.io/graphql-php/security/#limiting-query-depth
	 */
	public const GRAPHQL_MAX_QUERY_DEPTH = 3;

	/**
	 * GraphQLのインテロスペクションを無効化するかどうか
	 *
	 * @see https://webonyx.github.io/graphql-php/security/#disabling-introspection
	 */
	public const GRAPHQL_DISABLE_INTROSPECTION = true;

	/**
	 * GraphQLのmutationで許可される最大フィールド数
	 *
	 * mutation呼び出し時に許容するフィールド数の上限（独自ルール）
	 */
	public const GRAPHQL_MUTATION_FIELD_MAX_COUNT = 1;
}
