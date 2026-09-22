<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Baywall\Core\Application\Service\TransactionService;
use Baywall\Core\Infrastructure\Logging\ValueObject\LogCategory;
use Baywall\Core\Infrastructure\Logging\ValueObject\LogLevel;
use Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Baywall\Core\Infrastructure\WordPress\Repository\WpLogLevelRepository;

class V20251106_011_InitLogLevelWpOptions extends MigrationBase {


	public function __construct(
		private readonly TransactionService $transaction_service,
		private readonly WpLogLevelRepository $log_level_repository
	) {}

	public function version(): string {
		return '0.0.1';
	}

	public function up(): void {
		$this->transaction_service->transactional(
			function () {
				$this->log_level_repository->set( LogCategory::app(), LogLevel::info() );
				$this->log_level_repository->set( LogCategory::audit(), LogLevel::info() );
			}
		);
	}

	public function down(): void {
		$this->log_level_repository->deleteLogLevel( LogCategory::app() );
		$this->log_level_repository->deleteLogLevel( LogCategory::audit() );
	}
}
