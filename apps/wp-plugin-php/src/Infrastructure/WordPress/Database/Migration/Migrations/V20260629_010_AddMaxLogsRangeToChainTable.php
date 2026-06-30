<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\MyWpdb;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;

class V20260629_010_AddMaxLogsRangeToChainTable extends MigrationBase {

	private MyWpdb $wpdb;
	private string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->chain();
	}

	public function version(): string {
		return '0.0.3';
	}

	public function up(): void {
		// max_logs_range 列を追加
		$sql = <<<SQL
			ALTER TABLE `{$this->table_name}`
			ADD COLUMN `max_logs_range` int unsigned NOT NULL DEFAULT 150
			AFTER `confirmations`
		SQL;
		$this->wpdb->query( $sql );

		// 既存レコードの max_logs_range を 150 に更新
		$sql = <<<SQL
			UPDATE `{$this->table_name}`
			SET `max_logs_range` = 150
		SQL;
		$this->wpdb->query( $sql );
	}

	public function down(): void {
		// MySQL 5.7 でも動作するよう、information_schema で列の存在を確認してから削除
		$column_exists = $this->wpdb->get_var(
			"SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$this->table_name}' AND COLUMN_NAME = 'max_logs_range'"
		);

		if ( $column_exists ) {
			$sql = "ALTER TABLE `{$this->table_name}` DROP COLUMN `max_logs_range`";
			$this->wpdb->query( $sql );
		}
	}
}
