<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\Hooks;

use Cornix\Serendipity\Core\Application\Exception\LockAcquisitionException;
use Cornix\Serendipity\Core\Application\Service\LockService;
use Cornix\Serendipity\Core\Application\UseCase\CrawlAllAppContract;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\CronActionNameProvider;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\WpPluginInfoProvider;
use Cornix\Serendipity\Core\Constant\Config;
use Cornix\Serendipity\Core\Presentation\Hooks\Base\HookBase;
use Psr\Container\ContainerInterface;

/**
 * AppContractのイベントクロール処理をwp_cronを使って登録するクラス。
 *
 * Memo:
 * `wp_schedule_event`では、デフォルトで1時間に1回が一番短いサイクル。
 * `cron_schedules`フィルタで追加は可能だが、他プラグインとの競合等を考慮し`wp_schedule_single_event`を毎回登録する方法を採用。
 */
class AppContractCrawlCronHook extends HookBase {

	private ContainerInterface $container;
	private const LOCK_NAME = '05f69f89-24d8-4c72-b2a2-2c8ecfb24343'; // 排他制御用の適当な文字列

	public function __construct( ContainerInterface $container ) {
		$this->container = $container;
	}

	public function register(): void {
		// Cronアクション名を取得
		$action_name = $this->container->get( CronActionNameProvider::class )->appContractCrawl();

		// Appコントラクトのログをクロールするアクションを追加
		add_action( $action_name, array( $this, 'execute' ) );

		// プラグインが無効化された時に登録したアクションを削除
		register_deactivation_hook(
			$this->container->get( WpPluginInfoProvider::class )->mainFilePath(),
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
			$this->container->get( LockService::class )->withLock(
				self::LOCK_NAME,
				function () use ( $action_name ) {
					// 予約がされていない場合のみ登録
					if ( false === wp_next_scheduled( $action_name ) ) {
						$next_time = time() + Config::CRON_INTERVAL_APP_CONTRACT_CRAWL; // 次回の実行時刻
						$success   = wp_schedule_single_event( $next_time, $action_name );
						assert( $success === true, '[28D837C0] wp_schedule_single_event failed. ' . var_export( $success, true ) );
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
		$this->container->get( CrawlAllAppContract::class )->handle();
	}
}
