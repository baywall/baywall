<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Logging\AppLogger;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\MigrationLocator;
use Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject\PluginVersion;
use DI\Container;

/** データベースのマイグレーションを実行するクラス */
class Migrate {

	private MigrationLocator $locator;
	private AppLogger $logger;

	public function __construct( Container $container ) {
		$this->locator = new MigrationLocator( $container );
		$this->logger  = $container->get( AppLogger::class );
	}

	public function handle( string $prev_version, string $target_version ): void {
		$migrations = $this->locator->get( PluginVersion::from( $prev_version ), PluginVersion::from( $target_version ) );

		$executed = array();
		foreach ( $migrations as $migration ) {
			try {
				// マイグレーションを実行
				$migration->up();
				$executed[] = $migration; // 実行済み一覧に追加
				// 一番最初のマイグレーション処理でログレベルが設定されるので、ログ出力はそれ以降に行う
				$this->logger->info( '[E923FBC1] Applied migration: ' . get_class( $migration ) );
			} catch ( \Throwable $e ) {
				$this->logger->error( $e );
				$this->logger->info( '[B9A8BB31] Rolling back applied migrations...' );
				foreach ( array_reverse( $executed ) as $rollback ) {
					try {
						$this->logger->info( '[60697478] Rolling back migration: ' . get_class( $rollback ) );
						$rollback->down();
					} catch ( \Throwable $ex ) {
						// ロールバック中に発生したエラーは再スローせずにログ出力だけ行う
						$this->logger->error( $ex );
					}
				}
				throw $e;
			}
		}
	}
}
