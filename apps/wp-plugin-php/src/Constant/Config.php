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

	/** `block.json`へのパス */
	public const BLOCK_JSON_PATH = self::ROOT_DIR . '/build/block/block.json';

	/**
	 * PHPから渡される変数名
	 * ※ TypeScript側と整合性を取ること
	 */
	public const PHP_VAR_NAME = 'php_var_20792bdd';

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
}
