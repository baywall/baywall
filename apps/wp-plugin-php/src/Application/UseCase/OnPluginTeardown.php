<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Logging\AppLogger;
use Baywall\Core\Application\Service\PluginTeardownService;

/**
 * アンインストール時の処理を実行します
 */
class OnPluginTeardown {


	public function __construct(
		private readonly AppLogger $app_logger,
		private readonly PluginTeardownService $plugin_teardown_service
	) {}

	public function handle(): void {
		try {
			$this->plugin_teardown_service->teardown();
		} catch ( \Throwable $e ) {
			$this->app_logger->error( $e );
			throw $e;
		}
	}
}
