<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\MyWpdb;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;

class V20260612_021_AddIndexToUnlockPaywallTransaction extends MigrationBase {

	private MyWpdb $wpdb;
	private string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->unlockPaywallTransaction();
	}

	public function version(): string {
		return '0.0.2';
	}

	public function up(): void {
		$sql = "ALTER TABLE `{$this->table_name}` ADD INDEX idx_{$this->table_name}_B156B02C (`block_timestamp`)";
		$this->wpdb->query( $sql );
	}

	public function down(): void {
		$sql = "ALTER TABLE `{$this->table_name}` DROP INDEX idx_{$this->table_name}_B156B02C";
		$this->wpdb->query( $sql );
	}
}
