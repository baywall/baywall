<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Application\Logging\AppLogger;
use Cornix\Serendipity\Core\Application\Service\PluginMigrationService;
use Cornix\Serendipity\Core\Application\ValueObject\PluginVersion;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\MigrationLocator;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\WpInstalledPluginVersionRepository;

class WpPluginMigrationService implements PluginMigrationService {

	private WpInstalledPluginVersionRepository $plugin_version_option;
	private WpPluginInfoProvider $plugin_info_provider;
	private MigrationLocator $locator;
	private AppLogger $logger;

	public function __construct( WpInstalledPluginVersionRepository $plugin_version_option, WpPluginInfoProvider $plugin_info_provider, MigrationLocator $locator, AppLogger $logger ) {
		$this->plugin_version_option = $plugin_version_option;
		$this->plugin_info_provider  = $plugin_info_provider;
		$this->locator               = $locator;
		$this->logger                = $logger;
	}

	/** @inheritdoc */
	public function migrate(): void {
		// マイグレーションを実行
		$this->migrateWithVersion( $this->previousVersion(), $this->currentVersion() );
	}

	/** @inheritdoc */
	public function required(): bool {
		return version_compare( $this->previousVersion()->value(), $this->currentVersion()->value(), '<' );
	}

	/** 前回のインストール済みバージョンを取得します */
	private function previousVersion(): PluginVersion {
		$prev_version = $this->plugin_version_option->get();
		return $prev_version !== null ? $prev_version : PluginVersion::from( '0.0.0' );
	}

	/** 現在のプラグインバージョンを取得します */
	private function currentVersion(): PluginVersion {
		$current_version_str = $this->plugin_info_provider->version();
		return PluginVersion::from( $current_version_str );
	}

	/** バージョンを指定してマイグレーションを実行します */
	public function migrateWithVersion( PluginVersion $prev_version, PluginVersion $target_version ): void {
		$migrations = $this->locator->get( $prev_version, $target_version );

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

		// マイグレーションに成功した場合は、インストール済みバージョンを更新
		$this->plugin_version_option->update( $target_version );
	}
}
