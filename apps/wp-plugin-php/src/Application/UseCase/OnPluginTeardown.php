<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Logging\AppLogService;
use Baywall\Core\Application\Port\PluginTeardownService;

/**
 * アンインストール時の処理を実行します
 */
class OnPluginTeardown {
	public function __construct(
		private readonly AppLogService $app_log_service,
		private readonly PluginTeardownService $plugin_teardown_service
	) {}

	public function handle(): void {
		try {
			$this->plugin_teardown_service->teardown();
		} catch ( \Throwable $e ) {
			$this->app_log_service->error( $e );
			throw $e;
		}
	}
}
