<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\MyWpdb;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;

class V20251106_080_CreateServerSignerTable extends MigrationBase {

	private MyWpdb $wpdb;
	private string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->serverSigner();
	}

	public function version(): string {
		return '0.0.1';
	}

	public function up(): void {
		// `address`はウォレットの秘密鍵から生成可能だが、以下の目的で保持
		// - 秘密鍵からウォレットを作成したときの検証
		// - アドレスだけ参照する際の計算量削減
		// `base64_key`はウォレットの秘密鍵をBase64エンコードしたものを保存(そのままコピペでウォレットに登録されないようにしているだけ)

		// 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->table_name}` (
				`created_at`        timestamp      NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated_at`        timestamp      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`address`           varchar(191)   NOT NULL,
				`base64_key`        varchar(191)   NOT NULL,
				PRIMARY KEY (`address`)
			) {$this->wpdb->get_charset_collate()};
		SQL;
		$this->wpdb->dbh->query( $sql );
	}

	public function down(): void {
		$this->wpdb->dbh->query( "DROP TABLE IF EXISTS `{$this->table_name}`;" );
	}
}
