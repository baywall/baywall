<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Baywall\Core\Infrastructure\WordPress\Database\MyWpdb;
use Baywall\Core\Infrastructure\WordPress\Database\TableNameProvider;

class V20251106_040_CreateInvoiceTable extends MigrationBase {

	private readonly MyWpdb $wpdb;
	private readonly string $table_name;

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
				`created_at`             bigint           unsigned  NOT NULL,
				`updated_at`             bigint           unsigned  NOT NULL,
				`invoice_id`             varchar(191)               NOT NULL,
				`post_id`			     bigint           unsigned  NOT NULL,
				`chain_id`               bigint           unsigned  NOT NULL,
				`selling_amount`         decimal(65, 30)            NOT NULL,
				`selling_symbol`         varchar(191)               NOT NULL,
				`seller_address`         varchar(191)               NOT NULL,
				`payment_token_address`  varchar(191)               NOT NULL,
				`payment_token_symbol`   varchar(191)               NOT NULL,
				`payment_token_decimals` int                        NOT NULL,
				`payment_amount`         decimal(65, 0)             NOT NULL,
				`buyer_address`          varchar(191)               NOT NULL,
				-- @see Domain/ValueObject/InvoiceId（Crockford Base32。I/L/O/U は使わない）
				CONSTRAINT `chk_{$this->table_name}_invoice_id` CHECK (CONVERT(`invoice_id` USING utf8mb4) COLLATE utf8mb4_bin REGEXP '^[0-9A-HJKMNP-TV-Z]{26}$'),
				CONSTRAINT `chk_{$this->table_name}_seller_address` CHECK (CONVERT(`seller_address` USING utf8mb4) COLLATE utf8mb4_bin REGEXP '^0x[0-9a-f]{40}$'),
				CONSTRAINT `chk_{$this->table_name}_payment_token_address` CHECK (CONVERT(`payment_token_address` USING utf8mb4) COLLATE utf8mb4_bin REGEXP '^0x[0-9a-f]{40}$'),
				CONSTRAINT `chk_{$this->table_name}_payment_token_decimals` CHECK (`payment_token_decimals` BETWEEN 0 AND 18),
				CONSTRAINT `chk_{$this->table_name}_buyer_address` CHECK (CONVERT(`buyer_address` USING utf8mb4) COLLATE utf8mb4_bin REGEXP '^0x[0-9a-f]{40}$'),
				PRIMARY KEY (`invoice_id`),
				KEY `idx_{$this->table_name}_2D6F4376` (`created_at`),
				KEY `idx_{$this->table_name}_2068EC67` (`post_id`, `buyer_address`)
			) {$this->wpdb->get_charset_collate()};
		SQL;
		$this->wpdb->query( $sql );
	}

	public function down(): void {
		$this->wpdb->query( "DROP TABLE IF EXISTS `{$this->table_name}`;" );
	}
}
