<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\MyWpdb;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;

class V20251106_030_CreateChainTable extends MigrationBase {

	private MyWpdb $wpdb;
	private string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->chain();
	}

	public function version(): string {
		return '0.0.1';
	}

	public function up(): void {
		// - 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		// - `confirmations`は将来的に`latest`のような文字列が入る可能性があるため、`varchar(191)`とする
		$sql = <<<SQL
			CREATE TABLE `{$this->table_name}` (
				`created_at`                   timestamp               NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated_at`                   timestamp               NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`chain_id`                     bigint        unsigned  NOT NULL,
				`name`                         varchar(191)            NOT NULL,
				`network_category_id`          int           unsigned  NOT NULL,
				`rpc_url`                      varchar(191),
				`confirmations`                varchar(191)            NOT NULL,
				`block_explorer_url`           varchar(191),
				PRIMARY KEY (`chain_id`)
			) {$this->wpdb->get_charset_collate()};
		SQL;
		$this->wpdb->dbh->query( $sql );
	}

	public function down(): void {
		$this->wpdb->dbh->query( "DROP TABLE IF EXISTS `{$this->table_name}`;" );
	}
}
