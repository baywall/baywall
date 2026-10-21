<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Constant\Config;
use Baywall\Core\Application\Repository\LogRepository;

/**
 * 保持期間を超過したログレコードを削除します
 */
class CleanupOldLogs {

	public function __construct( private readonly LogRepository $log_repository ) {}

	public function handle(): void {
		$this->log_repository->deleteOldRecords( Config::LOG_DATA_EXPIRATION );
	}
}
