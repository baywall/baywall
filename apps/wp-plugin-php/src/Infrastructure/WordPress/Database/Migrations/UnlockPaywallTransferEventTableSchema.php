<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigratorBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;
use wpdb;


class UnlockPaywallTransferEventTableSchema extends MigratorBase {

	public function __construct( wpdb $wpdb, TableNameProvider $table_name_provider ) {
		parent::__construct( $wpdb, $table_name_provider->unlockPaywallTransferEvent() );
	}

	/** @inheritdoc */
	protected function versions(): array {
		return array(
			array( '0.0.1', UnlockPaywallTransferEventTableSchema_0_0_1::class ),
			// 他のバージョンのクラスを追加する場合はここに記述
		);
	}
}

// --------------------------------------------------------------------------------

/** @internal */
class UnlockPaywallTransferEventTableSchema_0_0_1 extends MigrationBase {
	public function up(): void {
		// - 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->tableName()}` (
				`created_at`     timestamp        NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`invoice_id`     varchar(191)     NOT NULL,
				`log_index`      int              NOT NULL,
				`from_address`   varchar(191)     NOT NULL,
				`to_address`     varchar(191)     NOT NULL,
				`token_address`  varchar(191)     NOT NULL,
				`amount`         decimal(65, 30)  NOT NULL,
				`transfer_type`  int              NOT NULL,
				PRIMARY KEY (`invoice_id`, `log_index`),
				KEY `idx_{$this->tableName()}_E1160E22` (`created_at`)
			) {$this->charset()};
		SQL;
		$this->query( $sql );
	}

	public function down(): void {
		$this->query( "DROP TABLE IF EXISTS `{$this->tableName()}`;" );
	}
}
