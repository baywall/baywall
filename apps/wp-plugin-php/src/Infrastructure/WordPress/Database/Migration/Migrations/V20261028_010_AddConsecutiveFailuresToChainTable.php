<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Baywall\Core\Infrastructure\WordPress\Database\Migration\Migration;
use Baywall\Core\Infrastructure\WordPress\Database\MyWpdb;
use Baywall\Core\Infrastructure\WordPress\Database\TableNameProvider;

class V20261028_010_AddConsecutiveFailuresToChainTable implements Migration {

	private readonly MyWpdb $wpdb;
	private readonly string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->chain();
	}

	public function version(): string {
		// 同梱リリースのプラグインバージョンと一致させる。
		// MigrationLocator が「保存済みバージョン < version() ≦ 現プラグインバージョン」で
		// 選択するため、それより小さい値だと既存環境で恒久的にスキップされる
		return '1.0.0-alpha.4';
	}

	public function up(): void {
		// クロールの連続失敗回数を記録する列を追加
		$sql = <<<SQL
			ALTER TABLE `{$this->table_name}`
			ADD COLUMN `consecutive_failures` int unsigned NOT NULL DEFAULT 0
			AFTER `max_logs_range`
		SQL;
		$this->wpdb->query( $sql );
	}

	public function down(): void {
		// MySQL 5.7 でも動作するよう、information_schema で列の存在を確認してから削除
		$column_exists = $this->wpdb->get_var(
			"SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$this->table_name}' AND COLUMN_NAME = 'consecutive_failures'"
		);

		if ( $column_exists ) {
			$sql = "ALTER TABLE `{$this->table_name}` DROP COLUMN `consecutive_failures`";
			$this->wpdb->query( $sql );
		}
	}
}
