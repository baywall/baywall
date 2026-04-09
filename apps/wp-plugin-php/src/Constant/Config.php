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
	public const ROOT_DIR = __DIR__ . '/../..';

	/**
	 * ERC-4361の署名用メッセージに含めるユーザー向けの説明文。
	 * トークンの有効期限や別ブラウザなど、認証情報が存在しない状況でリフレッシュトークンを発行する時に使用する。
	 *
	 * https://eips.ethereum.org/EIPS/eip-4361 には、
	 * > A human-readable ASCII assertion that the user will sign which MUST NOT include '\n' (the byte 0x0a).
	 * とあるので多言語対応は不要。
	 */
	public const ERC4361_STATEMENT = 'Sign this message to unlock the paywall.';

	/** GraphQLスキーマファイルへのパス */
	public const GRAPHQL_SCHEMA_PATH = self::ROOT_DIR . '/includes/assets/graphql/schema/schema.graphql';
	/** GraphQLスキーマのキャッシュファイル(PHPファイル)へのパス */
	public const GRAPHQL_CACHE_PATH = self::ROOT_DIR . '/includes/cache/graphql-schema.php';

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
