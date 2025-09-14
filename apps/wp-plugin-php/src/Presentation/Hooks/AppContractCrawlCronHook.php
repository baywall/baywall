<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\Hooks;

use Cornix\Serendipity\Core\Application\UseCase\CrawlAllAppContract;
use Cornix\Serendipity\Core\Repository\Name\CronActionName;
use Cornix\Serendipity\Core\Repository\PluginInfo;
use Cornix\Serendipity\Core\Constant\Config;
use Cornix\Serendipity\Core\Presentation\Hooks\Base\HookBase;

/**
 * AppContractのイベントクロール処理をwp_cronを使って登録するクラス。
 *
 * Memo:
 * `wp_schedule_event`では、デフォルトで1時間に1回が一番短いサイクル。
 * `cron_schedules`フィルタで追加は可能だが、他プラグインとの競合等を考慮し`wp_schedule_single_event`を毎回登録する方法を採用。
 */
class AppContractCrawlCronHook extends HookBase {

	private CrawlAllAppContract $crawl_all_app_contract;

	public function __construct( CrawlAllAppContract $crawl_all_app_contract ) {
		$this->crawl_all_app_contract = $crawl_all_app_contract;
	}

	public function register(): void {
		// Cronアクション名を取得
		$action_name = CronActionName::appContractCrawl();

		// Appコントラクトのログをクロールするアクションを追加
		add_action( $action_name, array( $this, 'execute' ) );

		// プラグインが無効化された時に登録したアクションを削除
		register_deactivation_hook(
			( new PluginInfo() )->mainFilePath(),
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
		// これは、予約を2つ以上登録する際の制限。
		// `wp_next_scheduled`でチェックして存在しない場合に登録する方法であれば、10分の制限は受けない。(30秒ごとに実行、のようなことも可能)
		//

		// 予約がされていない場合のみ登録
		if ( false === wp_next_scheduled( $action_name ) ) {
			$next_time = time() + Config::CRON_INTERVAL_APP_CONTRACT_CRAWL; // 次回の実行時刻

			$success = wp_schedule_single_event( $next_time, $action_name );
			assert( $success === true, '[28D837C0] wp_schedule_single_event failed. ' . var_export( $success, true ) );
		}
	}

	public function execute(): void {
		$this->crawl_all_app_contract->handle();
	}
}
