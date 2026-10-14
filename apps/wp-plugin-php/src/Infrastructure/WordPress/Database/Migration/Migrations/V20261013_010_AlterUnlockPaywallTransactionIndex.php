<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Baywall\Core\Infrastructure\WordPress\Database\MyWpdb;
use Baywall\Core\Infrastructure\WordPress\Database\TableNameProvider;

class V20261013_010_AlterUnlockPaywallTransactionIndex extends MigrationBase {

	private readonly MyWpdb $wpdb;
	private readonly string $table_name;
	private readonly string $index_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->unlockPaywallTransaction();
		$this->index_name = "idx_{$this->table_name}_B156B02C";
	}

	public function version(): string {
		// 同梱リリースのプラグインバージョンと一致させる。
		// MigrationLocator が「保存済みバージョン < version() ≦ 現プラグインバージョン」で
		// 選択するため、それより小さい値だと既存環境で恒久的にスキップされる
		return '1.0.0-alpha.3';
	}

	public function up(): void {
		// block_timestamp 単列インデックスを複合へ張り替える。
		// 同一 block_timestamp の行が transaction_hash 順に安定するため、
		// offset ページング時の行の重複・欠落を防ぐ。インデックス名は FORCE INDEX 句の互換のため維持
		$sql = <<<SQL
			ALTER TABLE `{$this->table_name}`
			DROP INDEX `{$this->index_name}`,
			ADD KEY `{$this->index_name}` (`block_timestamp`, `transaction_hash`)
		SQL;
		$this->wpdb->query( $sql );
	}

	public function down(): void {
		// MySQL 5.7 でも動作するよう、information_schema でインデックスの存在を確認してから復帰
		$index_exists = $this->wpdb->get_var(
			"SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$this->table_name}' AND INDEX_NAME = '{$this->index_name}'"
		);

		if ( $index_exists ) {
			$sql = "ALTER TABLE `{$this->table_name}` DROP INDEX `{$this->index_name}`, ADD KEY `{$this->index_name}` (`block_timestamp`)";
			$this->wpdb->query( $sql );
		}
	}
}
