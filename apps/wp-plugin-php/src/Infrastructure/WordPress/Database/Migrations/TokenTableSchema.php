<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base\MigratorBase;
use Cornix\Serendipity\Core\Repository\Name\TableNameProvider;
use wpdb;


class TokenTableSchema extends MigratorBase {

	public function __construct( wpdb $wpdb, TableNameProvider $table_name_provider ) {
		parent::__construct( $wpdb, $table_name_provider->token() );
	}

	/** @inheritdoc */
	protected function versions(): array {
		return array(
			array( '0.0.1', TokenTableSchema_0_0_1::class ),
			// 他のバージョンのクラスを追加する場合はここに記述
		);
	}
}

// --------------------------------------------------------------------------------

/** @internal */
class TokenTableSchema_0_0_1 extends MigrationBase {
	public function up(): void {
		// - 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->tableName()}` (
				`created_at`     timestamp               NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated_at`     timestamp               NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`chain_id`       bigint        unsigned  NOT NULL,
				`address`        varchar(191)            NOT NULL,
				`symbol`         varchar(191)            NOT NULL,
				`decimals`       int                     NOT NULL,
				`is_payable`     boolean                 NOT NULL,
				PRIMARY KEY (`chain_id`, `address`)
			) {$this->charset()};
		SQL;
		$this->query( $sql );
	}

	public function down(): void {
		$this->query( "DROP TABLE IF EXISTS `{$this->tableName()}`;" );
	}
}
