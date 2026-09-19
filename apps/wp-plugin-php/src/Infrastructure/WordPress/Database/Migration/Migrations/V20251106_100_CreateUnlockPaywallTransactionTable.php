<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Baywall\Core\Infrastructure\WordPress\Database\MyWpdb;
use Baywall\Core\Infrastructure\WordPress\Database\TableNameProvider;

class V20251106_100_CreateUnlockPaywallTransactionTable extends MigrationBase {

	private MyWpdb $wpdb;
	private string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->unlockPaywallTransaction();
	}

	public function version(): string {
		return '0.0.1';
	}

	public function up(): void {
		// 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->table_name}` (
				`created_at`          bigint        unsigned  NOT NULL,
				`invoice_id`          varchar(191)            NOT NULL,
				`chain_id`            bigint        unsigned  NOT NULL,
				`block_number`        bigint        unsigned  NOT NULL,
				`block_timestamp`     bigint        unsigned  NOT NULL,
				`transaction_hash`    varchar(191)            NOT NULL,
				`block_hash`          varchar(191)            NOT NULL,
				`removed`             boolean                 NOT NULL,
				-- @see Domain/ValueObject/InvoiceId（Crockford Base32。I/L/O/U は使わない）
				CONSTRAINT `chk_{$this->table_name}_invoice_id` CHECK (CONVERT(`invoice_id` USING utf8mb4) COLLATE utf8mb4_bin REGEXP '^[0-9A-HJKMNP-TV-Z]{26}$'),
				-- @see Domain/ValueObject/TransactionHash
				CONSTRAINT `chk_{$this->table_name}_transaction_hash` CHECK (CONVERT(`transaction_hash` USING utf8mb4) COLLATE utf8mb4_bin REGEXP '^0x[0-9a-f]{64}$'),
				-- @see Domain/ValueObject/BlockHash
				CONSTRAINT `chk_{$this->table_name}_block_hash` CHECK (CONVERT(`block_hash` USING utf8mb4) COLLATE utf8mb4_bin REGEXP '^0x[0-9a-f]{64}$'),
				PRIMARY KEY (`chain_id`, `transaction_hash`),
				KEY `idx_{$this->table_name}_1D00B82F` (`created_at`),
				KEY `idx_{$this->table_name}_B156B02C` (`block_timestamp`),
				KEY `idx_{$this->table_name}_593D7A4B` (`chain_id`, `block_number`),
				KEY `idx_{$this->table_name}_861702C8` (`invoice_id`)
			) {$this->wpdb->get_charset_collate()};
		SQL;
		$this->wpdb->query( $sql );
	}

	public function down(): void {
		$this->wpdb->query( "DROP TABLE IF EXISTS `{$this->table_name}`;" );
	}
}
