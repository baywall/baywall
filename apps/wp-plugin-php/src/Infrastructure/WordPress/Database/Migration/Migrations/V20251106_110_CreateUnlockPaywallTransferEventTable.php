<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Baywall\Core\Infrastructure\WordPress\Database\MyWpdb;
use Baywall\Core\Infrastructure\WordPress\Database\TableNameProvider;

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
				`created_at`        bigint unsigned  NOT NULL,
				`invoice_id`        varchar(191)     NOT NULL,
				`chain_id`          bigint unsigned  NOT NULL,
				`transaction_hash`  varchar(191)     NOT NULL,
				`log_index`         int              NOT NULL,
				`from_address`      varchar(191)     NOT NULL,
				`to_address`        varchar(191)     NOT NULL,
				`token_address`     varchar(191)     NOT NULL,
				`amount`            decimal(65, 0 )  NOT NULL,
				`transfer_type`     int              NOT NULL,
				-- @see Domain/ValueObject/InvoiceId（Crockford Base32。I/L/O/U は使わない）
				CONSTRAINT `chk_{$this->table_name}_invoice_id` CHECK (CONVERT(`invoice_id` USING utf8mb4) COLLATE utf8mb4_bin REGEXP '^[0-9A-HJKMNP-TV-Z]{26}$'),
				-- @see Domain/ValueObject/TransactionHash
				CONSTRAINT `chk_{$this->table_name}_transaction_hash` CHECK (CONVERT(`transaction_hash` USING utf8mb4) COLLATE utf8mb4_bin REGEXP '^0x[0-9a-f]{64}$'),
				CONSTRAINT `chk_{$this->table_name}_from_address` CHECK (CONVERT(`from_address` USING utf8mb4) COLLATE utf8mb4_bin REGEXP '^0x[0-9a-f]{40}$'),
				CONSTRAINT `chk_{$this->table_name}_to_address` CHECK (CONVERT(`to_address` USING utf8mb4) COLLATE utf8mb4_bin REGEXP '^0x[0-9a-f]{40}$'),
				CONSTRAINT `chk_{$this->table_name}_token_address` CHECK (CONVERT(`token_address` USING utf8mb4) COLLATE utf8mb4_bin REGEXP '^0x[0-9a-f]{40}$'),
				PRIMARY KEY (`chain_id`, `transaction_hash`, `log_index`),
				KEY `idx_{$this->table_name}_E1160E22` (`created_at`)
			) {$this->wpdb->get_charset_collate()};
		SQL;
		$this->wpdb->query( $sql );
	}

	public function down(): void {
		$this->wpdb->query( "DROP TABLE IF EXISTS `{$this->table_name}`;" );
	}
}
