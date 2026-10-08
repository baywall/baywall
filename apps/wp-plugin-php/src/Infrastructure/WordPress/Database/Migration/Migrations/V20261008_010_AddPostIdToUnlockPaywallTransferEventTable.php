<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Baywall\Core\Infrastructure\WordPress\Database\MyWpdb;
use Baywall\Core\Infrastructure\WordPress\Database\TableNameProvider;

class V20261008_010_AddPostIdToUnlockPaywallTransferEventTable extends MigrationBase {

	private readonly MyWpdb $wpdb;
	private readonly string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->unlockPaywallTransferEvent();
	}

	public function version(): string {
		// 同梱リリースのプラグインバージョンと一致させる。
		// MigrationLocator が「保存済みバージョン < version() ≦ 現プラグインバージョン」で
		// 選択するため、それより小さい値だと既存環境で恒久的にスキップされる
		return '1.0.0-alpha.3';
	}

	public function up(): void {
		// post_id 列を追加(イベントログ単体で記事単位の集計ができるようにする)
		$sql = <<<SQL
			ALTER TABLE `{$this->table_name}`
			ADD COLUMN `post_id` bigint unsigned NOT NULL AFTER `invoice_id`
		SQL;
		$this->wpdb->query( $sql );
	}

	public function down(): void {
		// MySQL 5.7 でも動作するよう、information_schema で列の存在を確認してから削除
		$column_exists = $this->wpdb->get_var(
			"SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$this->table_name}' AND COLUMN_NAME = 'post_id'"
		);

		if ( $column_exists ) {
			$sql = "ALTER TABLE `{$this->table_name}` DROP COLUMN `post_id`";
			$this->wpdb->query( $sql );
		}
	}
}
