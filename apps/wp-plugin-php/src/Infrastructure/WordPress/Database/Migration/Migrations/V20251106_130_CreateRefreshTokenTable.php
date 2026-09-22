<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Baywall\Core\Infrastructure\WordPress\Database\MyWpdb;
use Baywall\Core\Infrastructure\WordPress\Database\TableNameProvider;

class V20251106_130_CreateRefreshTokenTable extends MigrationBase {

	private readonly MyWpdb $wpdb;
	private readonly string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->refreshToken();
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
				`refresh_token_hash`  varchar(191)  NOT NULL,
				`wallet_address`      varchar(191)  NOT NULL,
				`expires_at`          bigint unsigned NOT NULL,
				`revoked_at`          bigint unsigned   NULL DEFAULT NULL,
				-- @see Infrastructure/WordPress/ValueObject/WpRefreshTokenHashString::checkWpRefreshTokenHashFormat()（`\.` は SQL 文字列リテラルで `.` に潰れるため `[.]` と書く）
				CONSTRAINT `chk_{$this->table_name}_refresh_token_hash` CHECK (CONVERT(`refresh_token_hash` USING utf8mb4) COLLATE utf8mb4_bin REGEXP '^[0-9]{17}[.][0-9a-f]{64}$'),
				CONSTRAINT `chk_{$this->table_name}_wallet_address` CHECK (CONVERT(`wallet_address` USING utf8mb4) COLLATE utf8mb4_bin REGEXP '^0x[0-9a-f]{40}$'),
				PRIMARY KEY (`refresh_token_hash`),
				KEY `idx_{$this->table_name}_60F0390C` (`created_at`),
				KEY `idx_{$this->table_name}_F99C6F43` (`wallet_address`)
			) {$this->wpdb->get_charset_collate()};
		SQL;
		$this->wpdb->query( $sql );
	}

	public function down(): void {
		$this->wpdb->query( "DROP TABLE IF EXISTS `{$this->table_name}`;" );
	}
}
