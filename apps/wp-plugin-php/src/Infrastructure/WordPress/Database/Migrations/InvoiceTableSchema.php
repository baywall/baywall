<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigratorBase;
use Cornix\Serendipity\Core\Repository\Name\TableNameProvider;
use wpdb;


class InvoiceTableSchema extends MigratorBase {

	public function __construct( wpdb $wpdb, TableNameProvider $table_name_provider ) {
		parent::__construct( $wpdb, $table_name_provider->invoice() );
	}

	/** @inheritdoc */
	protected function versions(): array {
		return array(
			array( '0.0.1', InvoiceTableSchema_0_0_1::class ),
			// 他のバージョンのクラスを追加する場合はここに記述
		);
	}
}

// --------------------------------------------------------------------------------

/** @internal */
class InvoiceTableSchema_0_0_1 extends MigrationBase {
	public function up(): void {
		$index_name = "idx_{$this->tableName()}_2D6F4376";
		// - 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->tableName()}` (
				`created_at`             timestamp                  NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated_at`             timestamp                  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`id`                     varchar(191)               NOT NULL,
				`post_id`			     bigint           unsigned  NOT NULL,
				`chain_id`               bigint           unsigned  NOT NULL,
				`selling_amount`         decimal(65, 30)            NOT NULL,
				`selling_symbol`         varchar(191)               NOT NULL,
				`seller_address`         varchar(191)               NOT NULL,
				`payment_token_address`  varchar(191)               NOT NULL,
				`payment_amount`         decimal(65, 30)            NOT NULL,
				`consumer_address`       varchar(191)               NOT NULL,
				`nonce`                  varchar(191)               DEFAULT NULL,
				PRIMARY KEY (`id`),
				KEY `{$index_name}` (`created_at`)
			) {$this->charset()};
		SQL;
		$this->query( $sql );
	}

	public function down(): void {
		$this->query( "DROP TABLE IF EXISTS `{$this->tableName()}`;" );
	}
}
