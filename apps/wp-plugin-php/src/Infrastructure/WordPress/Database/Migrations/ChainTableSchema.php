<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigratorBase;
use Cornix\Serendipity\Core\Repository\Name\TableNameProvider;
use wpdb;


class ChainTableSchema extends MigratorBase {

	public function __construct( wpdb $wpdb, TableNameProvider $table_name_provider ) {
		parent::__construct( $wpdb, $table_name_provider->chain() );
	}

	/** @inheritdoc */
	protected function versions(): array {
		return array(
			array( '0.0.1', ChainTableSchema_0_0_1::class ),
			// 他のバージョンのクラスを追加する場合はここに記述
		);
	}
}

// --------------------------------------------------------------------------------

/** @internal */
class ChainTableSchema_0_0_1 extends MigrationBase {
	public function up(): void {
		// - 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		// - `confirmations`は将来的に`latest`のような文字列が入る可能性があるため、`varchar(191)`とする
		$sql = <<<SQL
			CREATE TABLE `{$this->tableName()}` (
				`created_at`                   timestamp               NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated_at`                   timestamp               NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`chain_id`                     bigint        unsigned  NOT NULL,
				`name`                         varchar(191)            NOT NULL,
				`network_category_id`          int           unsigned  NOT NULL,
				`rpc_url`                      varchar(191),
				`confirmations`                varchar(191)            NOT NULL,
				`block_explorer_url`           varchar(191),
				PRIMARY KEY (`chain_id`)
			) {$this->charset()};
		SQL;
		$this->query( $sql );
	}

	public function down(): void {
		$this->query( "DROP TABLE IF EXISTS `{$this->tableName()}`;" );
	}
}
