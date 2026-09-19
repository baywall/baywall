<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Baywall\Core\Infrastructure\WordPress\Database\MyWpdb;
use Baywall\Core\Infrastructure\WordPress\Database\TableNameProvider;

class V20251106_015_CreateLogTable extends MigrationBase {

	private MyWpdb $wpdb;
	private string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->log();
	}

	public function version(): string {
		return '0.0.1';
	}

	public function up(): void {
		// 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->table_name}` (
				`log_id`     bigint unsigned  NOT NULL AUTO_INCREMENT,
				`created_at` bigint unsigned  NOT NULL,
				`level`      varchar(20)      NOT NULL,
				`category`   varchar(50)      NOT NULL,
				`message`    mediumtext       NOT NULL,
				PRIMARY KEY (`log_id`),
				KEY `idx_{$this->table_name}_7A3E9B41` (`created_at`),
				KEY `idx_{$this->table_name}_D5C24F08` (`level`, `created_at`),
				KEY `idx_{$this->table_name}_08F61A7D` (`category`, `created_at`)
			) {$this->wpdb->get_charset_collate()};
		SQL;
		$this->wpdb->query( $sql );
	}

	public function down(): void {
		$this->wpdb->query( "DROP TABLE IF EXISTS `{$this->table_name}`;" );
	}
}
