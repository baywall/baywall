<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Baywall\Core\Infrastructure\WordPress\Database\MyWpdb;
use Baywall\Core\Infrastructure\WordPress\Database\TableNameProvider;

class V20251106_070_CreateSellerTable extends MigrationBase {

	private readonly MyWpdb $wpdb;
	private readonly string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->seller();
	}

	public function version(): string {
		return '0.0.1';
	}

	public function up(): void {
		// 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->table_name}` (
				`created_at`            bigint        unsigned  NOT NULL,
				`updated_at`            bigint        unsigned  NOT NULL,
				`seller_address`        varchar(191)            NOT NULL,
				`signing_message`       text                    NOT NULL,
				`signature`             varchar(191)            NOT NULL,
				CONSTRAINT `chk_{$this->table_name}_seller_address` CHECK (CONVERT(`seller_address` USING utf8mb4) COLLATE utf8mb4_bin REGEXP '^0x[0-9a-f]{40}$'),
				-- @see Domain/ValueObject/Signature
				CONSTRAINT `chk_{$this->table_name}_signature` CHECK (CONVERT(`signature` USING utf8mb4) COLLATE utf8mb4_bin REGEXP '^0x[0-9a-f]{130}$'),
				PRIMARY KEY (`seller_address`)
			) {$this->wpdb->get_charset_collate()};
		SQL;
		$this->wpdb->query( $sql );
	}

	public function down(): void {
		$this->wpdb->query( "DROP TABLE IF EXISTS `{$this->table_name}`;" );
	}
}
