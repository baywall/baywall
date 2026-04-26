<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\MyWpdb;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;

class V20251106_110_CreateUnlockPaywallTransferEventTable extends MigrationBase {

	private MyWpdb $wpdb;
	private string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->unlockPaywallTransferEvent();
	}

	public function version(): string {
		return '0.0.1';
	}

	public function up(): void {
		// 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->table_name}` (
				`created_at`     timestamp        NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`invoice_id`     varchar(191)     NOT NULL,
				`log_index`      int              NOT NULL,
				`from_address`   varchar(191)     NOT NULL,
				`to_address`     varchar(191)     NOT NULL,
				`token_address`  varchar(191)     NOT NULL,
				`amount`         decimal(35, 0 )  NOT NULL,
				`transfer_type`  int              NOT NULL,
				CONSTRAINT `chk_{$this->table_name}_from_address` CHECK (`from_address` REGEXP '^0x[0-9a-f]{40}$'),
				CONSTRAINT `chk_{$this->table_name}_to_address` CHECK (`to_address` REGEXP '^0x[0-9a-f]{40}$'),
				CONSTRAINT `chk_{$this->table_name}_token_address` CHECK (`token_address` REGEXP '^0x[0-9a-f]{40}$'),
				PRIMARY KEY (`invoice_id`, `log_index`),
				KEY `idx_{$this->table_name}_E1160E22` (`created_at`),
				KEY `idx_{$this->table_name}_05504FB6` (`invoice_id`, `transfer_type`)
			) {$this->wpdb->get_charset_collate()};
		SQL;
		$this->wpdb->query( $sql );
	}

	public function down(): void {
		$this->wpdb->query( "DROP TABLE IF EXISTS `{$this->table_name}`;" );
	}
}
