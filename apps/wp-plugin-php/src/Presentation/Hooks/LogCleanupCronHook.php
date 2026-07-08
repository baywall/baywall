<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\Hooks;

use Cornix\Serendipity\Core\Application\Exception\LockAcquisitionException;
use Cornix\Serendipity\Core\Application\Service\LockService;
use Cornix\Serendipity\Core\Constant\Config;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Constants\WpCronName;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway\LogTable;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpPluginInfoProvider;
use Cornix\Serendipity\Core\Presentation\Hooks\Base\HookBase;
use Psr\Container\ContainerInterface;

/**
 * ログテーブルのクリーンアップ処理をwp_cronを使って登録するクラス。
 *
 * Memo:
 * `wp_schedule_event`では、デフォルトで1時間に1回が一番短いサイクル。
 * `cron_schedules`フィルタで追加は可能だが、他プラグインとの競合等を考慮し`wp_schedule_single_event`を毎回登録する方法を採用。
 */
class LogCleanupCronHook extends HookBase {

	private ContainerInterface $container;
	private const LOCK_NAME = 'E1AE6C84'; // 排他制御用の適当な文字列

	public function __construct( ContainerInterface $container ) {
		$this->container = $container;
	}

	public function register(): void {
		// Cronアクション名を取得
		$action_name = WpCronName::LOG_CLEANUP;

		// ログクリーンアップのアクションを追加
		add_action( $action_name, array( $this, 'execute' ) );

		// プラグインが無効化された時に登録したアクションを削除
		/** @var WpPluginInfoProvider */
		$plugin_info_provider = $this->container->get( WpPluginInfoProvider::class );
		register_deactivation_hook(
			$plugin_info_provider->mainFilePath(),
			function () use ( $action_name ) {
				wp_clear_scheduled_hook( $action_name );
			}
		);

		// Cronアクションを登録
		$this->registerSchedule( $action_name );
	}

	/**
	 * Cronアクションを登録します。
	 */
	private function registerSchedule( string $action_name ): void {
		//
		// `wp_schedule_single_event`は、同一のアクション名、同一の引数の場合、登録できる時間に制限がある。
		// => https://developer.wordpress.org/reference/functions/wp_schedule_single_event/
		// > Note that scheduling an event to occur within 10 minutes of an existing event with the same action hook
		// > will be ignored unless you pass unique $args values for each scheduled event.
		// これは重複予約時の制限。
		// なお、`wp_schedule_single_event`で登録した単発イベントは実行されると消費されるため、
		// 同一アクション名の次回予約は未登録状態に戻る前提で都度再登録する。
		// 10分ルール自体は常に有効だが、`wp_next_scheduled`で既存イベントの有無を確認し、
		// 未登録のときに1件だけ登録する方式であれば、重複による拒否を避けやすい。
		//

		try {
			/** @var LockService */
			$lock_service = $this->container->get( LockService::class );
			$lock_service->withLock(
				self::LOCK_NAME,
				function () use ( $action_name ) {
					// 予約がされていない場合のみ登録
					if ( false === wp_next_scheduled( $action_name ) ) {
						$next_time = $this->getNextRunTime( new \DateTimeImmutable( 'now', wp_timezone() ) );
						$success   = wp_schedule_single_event( $next_time, $action_name );
						assert( $success === true, '[9D3C7A2F] wp_schedule_single_event failed. ' . var_export( $success, true ) );
					}
				}
			);
		} catch ( LockAcquisitionException $e ) {
			// ロックの取得に失敗した場合（同時リクエスト時）は、
			// 他のリクエストが登録処理を行っているはずなので何もせず終了する
			// Do Nothing.
		}
	}

	public function execute(): void {
		try {
			/** @var LogTable */
			$log_table = $this->container->get( LogTable::class );
			$log_table->deleteOldRecords( Config::LOG_DATA_EXPIRATION );
		} finally {
			try {
				$this->registerSchedule( WpCronName::LOG_CLEANUP );
			} catch ( \Throwable $e ) {
				// 再予約失敗はログに記録するが、元の例外をマスキングしない
				error_log( '[9B98948F] LogCleanupCronHook reschedule failed: ' . $e->getMessage() );
			}
		}
	}

	/**
	 * 次回実行時刻の Unix タイムスタンプを取得します。
	 * 現在時刻が午前3:00より前なら「今日の午前3:00」、それ以降なら「翌日の午前3:00」を返します。
	 *
	 * @param \DateTimeImmutable $now 基準とする現在時刻。
	 */
	private function getNextRunTime( \DateTimeImmutable $now ): int {
		$tz_now   = $now->setTimezone( wp_timezone() );
		$three_am = $tz_now->setTime( 3, 0 );
		if ( $tz_now < $three_am ) {
			return $three_am->getTimestamp();
		}
		return $three_am->modify( '+1 day' )->getTimestamp();
	}
}
