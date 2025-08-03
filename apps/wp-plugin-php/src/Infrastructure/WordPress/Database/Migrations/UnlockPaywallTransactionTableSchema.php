<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigratorBase;
use Cornix\Serendipity\Core\Repository\Name\TableName;
use wpdb;


class UnlockPaywallTransactionTableSchema extends MigratorBase {

	public function __construct( wpdb $wpdb, TableName $table_name_provider ) {
		parent::__construct( $wpdb, $table_name_provider->unlockPaywallTransaction() );
	}

	/** @inheritdoc */
	protected function versions(): array {
		return array(
			array( '0.0.1', UnlockPaywallTransactionTableSchema_0_0_1::class ),
			// 他のバージョンのクラスを追加する場合はここに記述
		);
	}
}

// --------------------------------------------------------------------------------

/** @internal */
class UnlockPaywallTransactionTableSchema_0_0_1 extends MigrationBase {
	public function up(): void {
		// - 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->tableName()}` (
				`created_at`          timestamp               NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`invoice_id`          varchar(191)            NOT NULL,
				`chain_id`            bigint        unsigned  NOT NULL,
				`block_number`        bigint        unsigned  NOT NULL,
				`transaction_hash`    varchar(191)            NOT NULL,
				PRIMARY KEY (`invoice_id`),
				KEY `idx_{$this->tableName()}_1D00B82F` (`created_at`)
			) {$this->charset()};
		SQL;
		$this->query( $sql );
	}

	public function down(): void {
		$this->query( "DROP TABLE IF EXISTS `{$this->tableName()}`;" );
	}
}
