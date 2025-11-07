<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Cornix\Serendipity\Core\Application\Service\TransactionService;
use Cornix\Serendipity\Core\Infrastructure\Logging\ValueObject\LogCategory;
use Cornix\Serendipity\Core\Infrastructure\Logging\ValueObject\LogLevel;
use Cornix\Serendipity\Core\Infrastructure\System\Environment;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Logging\WpLogLevelProvider;
use Throwable;

class V20251106_011_InitLogLevelWpOptions extends MigrationBase {

	private TransactionService $transaction_service;
	private Environment $environment;
	private WpLogLevelProvider $log_level_provider;

	public function __construct( TransactionService $transaction_service, Environment $environment, WpLogLevelProvider $log_level_provider ) {
		$this->transaction_service = $transaction_service;
		$this->environment         = $environment;
		$this->log_level_provider  = $log_level_provider;
	}

	public function version(): string {
		return '0.0.1';
	}

	public function up(): void {
		$this->transaction_service->transactional(
			function () {
				if ( $this->environment->isProduction() ) {
					// 本番環境のログレベル
					$this->log_level_provider->setLogLevel( LogCategory::app(), LogLevel::info() );
					$this->log_level_provider->setLogLevel( LogCategory::audit(), LogLevel::debug() ); // 監査ログは詳細に出力
				} elseif ( $this->environment->isDevelopment() ) {
					// 開発時のログレベル
					$this->log_level_provider->setLogLevel( LogCategory::app(), LogLevel::debug() );
					$this->log_level_provider->setLogLevel( LogCategory::audit(), LogLevel::debug() );
				} elseif ( $this->environment->isTesting() ) {
					// テスト時のログレベル(ほぼ出力しなくてよい)
					$this->log_level_provider->setLogLevel( LogCategory::app(), LogLevel::error() );
					$this->log_level_provider->setLogLevel( LogCategory::audit(), LogLevel::error() );
				} else {
					throw new \RuntimeException( '[1D882958] Unsupported environment' );
				}
			}
		);
	}

	public function down(): void {
		$this->log_level_provider->deleteLogLevel( LogCategory::app() );
		$this->log_level_provider->deleteLogLevel( LogCategory::audit() );
	}
}
