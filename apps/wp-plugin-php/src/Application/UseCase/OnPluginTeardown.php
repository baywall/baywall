<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Logging\AppLogger;
use Cornix\Serendipity\Core\Application\Service\PluginTeardownService;

/**
 * アンインストール時の処理を実行します
 */
class OnPluginTeardown {

	private AppLogger $app_logger;
	private PluginTeardownService $plugin_teardown_service;

	public function __construct(
		AppLogger $app_logger,
		PluginTeardownService $plugin_teardown_service
	) {
		$this->app_logger              = $app_logger;
		$this->plugin_teardown_service = $plugin_teardown_service;
	}

	public function handle(): void {
		try {
			$this->plugin_teardown_service->teardown();
		} catch ( \Throwable $e ) {
			$this->app_logger->error( $e );
			throw $e;
		}
	}
}
