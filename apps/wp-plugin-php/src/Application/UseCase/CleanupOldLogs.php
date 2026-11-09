<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Logging\AppLogService;
use Baywall\Core\Constant\Config;
use Baywall\Core\Application\Repository\LogRepository;

/**
 * 保持期間を超過したログレコードを削除します
 */
class CleanupOldLogs {
	public function __construct(
		private readonly AppLogService $app_log_service,
		private readonly LogRepository $log_repository
	) {}

	public function handle(): void {
		$this->log_repository->deleteOldRecords( Config::LOG_DATA_EXPIRATION );

		// 削除件数は LogRepository::deleteOldRecords() が void のため取得できない
		$this->app_log_service->info( '[0AB88285] Old log records cleanup finished. retention seconds: ' . Config::LOG_DATA_EXPIRATION );
	}
}
