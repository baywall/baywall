<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Baywall\Core\Infrastructure\WordPress\Database\Migration\Migration;
use Baywall\Core\Infrastructure\WordPress\Database\MyWpdb;
use Baywall\Core\Infrastructure\WordPress\Database\TableNameProvider;

class V20261106_010_CreateLogAuditTable implements Migration {

	private readonly MyWpdb $wpdb;
	private readonly string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->logAudit();
	}

	public function version(): string {
		return '1.0.0-alpha.4';
	}

	public function up(): void {
		// 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->table_name}` (
				`audit_id`       bigint unsigned  NOT NULL AUTO_INCREMENT,
				`created_at`     bigint unsigned  NOT NULL,
				`plugin_version` varchar(191)     NOT NULL,
				`user_id`        bigint unsigned  NOT NULL,
				`level`          varchar(20)      NOT NULL,
				`action`         varchar(191)     NOT NULL,
				`message`        mediumtext       NOT NULL,
				PRIMARY KEY (`audit_id`),
				KEY `idx_{$this->table_name}_9CF2AFBC` (`created_at`),
				KEY `idx_{$this->table_name}_DD672662` (`user_id`, `created_at`),
				KEY `idx_{$this->table_name}_4A67D999` (`action`, `created_at`)
			) {$this->wpdb->get_charset_collate()};
		SQL;
		$this->wpdb->query( $sql );
	}

	public function down(): void {
		$this->wpdb->query( "DROP TABLE IF EXISTS `{$this->table_name}`;" );
	}
}
