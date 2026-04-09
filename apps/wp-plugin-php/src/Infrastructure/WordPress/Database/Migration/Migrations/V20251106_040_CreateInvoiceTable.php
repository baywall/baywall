<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\MyWpdb;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;

class V20251106_040_CreateInvoiceTable extends MigrationBase {

	private MyWpdb $wpdb;
	private string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->invoice();
	}

	public function version(): string {
		return '0.0.1';
	}

	public function up(): void {
		// 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->table_name}` (
				`created_at`             timestamp                  NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated_at`             timestamp                  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`id`                     varchar(191)               NOT NULL,
				`post_id`			     bigint           unsigned  NOT NULL,
				`chain_id`               bigint           unsigned  NOT NULL,
				`selling_amount`         decimal(65, 30)            NOT NULL,
				`selling_symbol`         varchar(191)               NOT NULL,
				`seller_address`         varchar(191)               NOT NULL,
				`payment_token_address`  varchar(191)               NOT NULL,
				`payment_amount`         decimal(35, 0)             NOT NULL,
				`customer_address`       varchar(191)               NOT NULL,
				CONSTRAINT `chk_{$this->table_name}_seller_address` CHECK (`seller_address` REGEXP '^0x[0-9a-f]{40}$'),
				CONSTRAINT `chk_{$this->table_name}_payment_token_address` CHECK (`payment_token_address` REGEXP '^0x[0-9a-f]{40}$'),
				CONSTRAINT `chk_{$this->table_name}_customer_address` CHECK (`customer_address` REGEXP '^0x[0-9a-f]{40}$'),
				PRIMARY KEY (`id`),
				KEY `idx_{$this->table_name}_2D6F4376` (`created_at`),
				KEY `idx_{$this->table_name}_6970C683` (`id`, `chain_id`, `payment_token_address`),
				KEY `idx_{$this->table_name}_2068EC67` (`post_id`, `customer_address`)
			) {$this->wpdb->get_charset_collate()};
		SQL;
		$this->wpdb->dbh->query( $sql );
	}

	public function down(): void {
		$this->wpdb->dbh->query( "DROP TABLE IF EXISTS `{$this->table_name}`;" );
	}
}
