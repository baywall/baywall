<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Constant\Config;
use Baywall\Core\Infrastructure\WordPress\Database\TableGateway\LogTable;

/**
 * 保持期間を超過したログレコードを削除します
 */
class CleanupOldLogs {

	public function __construct( private readonly LogTable $log_table ) {}

	public function handle(): void {
		$this->log_table->deleteOldRecords( Config::LOG_DATA_EXPIRATION );
	}
}
