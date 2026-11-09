<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Baywall\Core\Application\Service\TransactionRunner;
use Baywall\Core\Application\Logging\ValueObject\LogLevel;
use Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Baywall\Core\Infrastructure\WordPress\Database\OptionRepository\WpAppLogLevelRepository;

class V20251106_011_InitLogLevelWpOptions extends MigrationBase {
	public function __construct(
		private readonly TransactionRunner $transaction_runner,
		private readonly WpAppLogLevelRepository $app_log_level_repository
	) {}

	public function version(): string {
		return '0.0.1';
	}

	public function up(): void {
		$this->transaction_runner->transactional(
			function () {
				$this->app_log_level_repository->set( LogLevel::info() );
			}
		);
	}

	public function down(): void {
		$this->app_log_level_repository->deleteLogLevel();
	}
}
