<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Baywall\Core\Infrastructure\WordPress\Database\MyWpdb;
use Baywall\Core\Infrastructure\WordPress\Database\TableNameProvider;

class V20261001_010_AlterErc4361NonceCheck extends MigrationBase {

	private readonly MyWpdb $wpdb;
	private readonly string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->erc4361Nonce();
	}

	public function version(): string {
		// 同梱リリースのプラグインバージョンと一致させる。
		// MigrationLocator が「保存済みバージョン < version() ≦ 現プラグインバージョン」で
		// 選択するため、それより小さい値だと既存環境で恒久的にスキップされる
		return '1.0.0-alpha.2';
	}

	public function up(): void {
		// nonce 生成を 96bit へ拡大するため（Base62 の 15〜17 文字）、CHECK 制約の上限を 17 に広げる
		// 下限 8 は旧生成値（10〜11 文字）のレコードを受理し続けるため維持する
		// CHECK の削除は `DROP CHECK` ではなく `DROP CONSTRAINT` を使う（`DROP CHECK` は MariaDB で構文エラーになる）
		$sql = <<<SQL
			ALTER TABLE `{$this->table_name}` DROP CONSTRAINT `chk_{$this->table_name}_erc4361_nonce`
		SQL;
		$this->wpdb->query( $sql );

		$sql = <<<SQL
			ALTER TABLE `{$this->table_name}` ADD CONSTRAINT `chk_{$this->table_name}_erc4361_nonce` CHECK (CONVERT(`erc4361_nonce` USING utf8mb4) COLLATE utf8mb4_bin REGEXP '^[0-9A-Za-z]{8,17}$')
		SQL;
		$this->wpdb->query( $sql );
	}

	public function down(): void {
		// down() は up() 成功後・同一バッチの後続マイグレーションが失敗したときの
		// ロールバックで呼ばれる。その直後（同一リクエスト内）では 16〜17 文字の
		// レコードは存在しないため、旧上限 {8,11} への復帰で CHECK 違反にならない
		//
		// 制約の存在確認は down() の冪等性のため（再実行・途中失敗状態での手動実行に耐える）
		$constraint_exists = $this->wpdb->get_var(
			"SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = '{$this->table_name}' AND CONSTRAINT_NAME = 'chk_{$this->table_name}_erc4361_nonce'"
		);

		if ( $constraint_exists ) {
			$sql = "ALTER TABLE `{$this->table_name}` DROP CONSTRAINT `chk_{$this->table_name}_erc4361_nonce`";
			$this->wpdb->query( $sql );
		}

		$sql = <<<SQL
			ALTER TABLE `{$this->table_name}` ADD CONSTRAINT `chk_{$this->table_name}_erc4361_nonce` CHECK (CONVERT(`erc4361_nonce` USING utf8mb4) COLLATE utf8mb4_bin REGEXP '^[0-9A-Za-z]{8,11}$')
		SQL;
		$this->wpdb->query( $sql );
	}
}
