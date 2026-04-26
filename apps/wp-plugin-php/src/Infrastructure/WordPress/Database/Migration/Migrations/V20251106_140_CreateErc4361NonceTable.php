<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\MyWpdb;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;

class V20251106_140_CreateErc4361NonceTable extends MigrationBase {

	private MyWpdb $wpdb;
	private string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->erc4361Nonce();
	}

	public function version(): string {
		return '0.0.1';
	}

	public function up(): void {
		// 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		//
		// レコードがすぐ削除されることに加え、このテーブル自体の使用頻度が低いため
		// ウォレットアドレスをキーにして問題ないと判断している
		//
		// nonceに関しては、第三者が取得したとしても署名を作成することができないのでハッシュ化は不要
		$sql = <<<SQL
			CREATE TABLE `{$this->table_name}` (
				`created_at`          timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated_at`          timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`wallet_address`      varchar(191)  NOT NULL,
				`erc4361_nonce`       varchar(191)  NOT NULL,
				`issued_at`           timestamp     NOT NULL,
				CONSTRAINT `chk_{$this->table_name}_wallet_address` CHECK (`wallet_address` REGEXP '^0x[0-9a-f]{40}$'),
				PRIMARY KEY (`wallet_address`),
				KEY `idx_{$this->table_name}_C56F9034` (`issued_at`)
			) {$this->wpdb->get_charset_collate()};
		SQL;
		$this->wpdb->query( $sql );
	}

	public function down(): void {
		$this->wpdb->query( "DROP TABLE IF EXISTS `{$this->table_name}`;" );
	}
}
