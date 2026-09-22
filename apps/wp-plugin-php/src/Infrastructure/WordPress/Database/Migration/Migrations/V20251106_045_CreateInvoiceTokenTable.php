<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Baywall\Core\Infrastructure\WordPress\Database\MyWpdb;
use Baywall\Core\Infrastructure\WordPress\Database\TableNameProvider;

class V20251106_045_CreateInvoiceTokenTable extends MigrationBase {

	private readonly MyWpdb $wpdb;
	private readonly string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->invoiceToken();
	}

	public function version(): string {
		return '0.0.1';
	}

	public function up(): void {
		// 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->table_name}` (
				`created_at`          bigint unsigned NOT NULL,
				`updated_at`          bigint unsigned NOT NULL,
				`invoice_id`          varchar(191)  NOT NULL,
				`invoice_token_hash`  varchar(191)  NOT NULL,
				`expires_at`          bigint unsigned NOT NULL,
				`revoked_at`          bigint unsigned   NULL DEFAULT NULL,
				-- @see Domain/ValueObject/InvoiceId（Crockford Base32。I/L/O/U は使わない）
				CONSTRAINT `chk_{$this->table_name}_invoice_id` CHECK (CONVERT(`invoice_id` USING utf8mb4) COLLATE utf8mb4_bin REGEXP '^[0-9A-HJKMNP-TV-Z]{26}$'),
				-- @see Infrastructure/WordPress/ValueObject/WpInvoiceTokenHashString::checkWpInvoiceTokenHashFormat()（`\.` は SQL 文字列リテラルで `.` に潰れるため `[.]` と書く）
				CONSTRAINT `chk_{$this->table_name}_invoice_token_hash` CHECK (CONVERT(`invoice_token_hash` USING utf8mb4) COLLATE utf8mb4_bin REGEXP '^[0-9]{17}[.][0-9a-f]{64}$'),
				PRIMARY KEY (`invoice_token_hash`),
				KEY `idx_{$this->table_name}_C9AF5E8B` (`created_at`),
				KEY `idx_{$this->table_name}_60BBA227` (`invoice_id`)
			) {$this->wpdb->get_charset_collate()};
		SQL;
		$this->wpdb->query( $sql );
	}

	public function down(): void {
		$this->wpdb->query( "DROP TABLE IF EXISTS `{$this->table_name}`;" );
	}
}
