<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Cornix\Serendipity\Core\Application\Service\TransactionService;
use Cornix\Serendipity\Core\Infrastructure\Logging\ValueObject\LogCategory;
use Cornix\Serendipity\Core\Infrastructure\Logging\ValueObject\LogLevel;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Repository\WpLogLevelRepository;

class V20251106_011_InitLogLevelWpOptions extends MigrationBase {

	private TransactionService $transaction_service;
	private WpLogLevelRepository $log_level_repository;

	public function __construct( TransactionService $transaction_service, WpLogLevelRepository $log_level_repository ) {
		$this->transaction_service  = $transaction_service;
		$this->log_level_repository = $log_level_repository;
	}

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
