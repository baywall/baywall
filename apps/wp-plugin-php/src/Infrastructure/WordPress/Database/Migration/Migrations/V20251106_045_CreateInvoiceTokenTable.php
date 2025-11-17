<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\MyWpdb;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableNameProvider;

class V20251106_045_CreateInvoiceTokenTable extends MigrationBase {

	private MyWpdb $wpdb;
	private string $table_name;

	public function __construct( MyWpdb $wpdb, TableNameProvider $table_name_provider ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name_provider->invoiceToken();
	}

	public function version(): string {
		return '0.0.1';
	}

	public function up(): void {
		// 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		//
		// 一つの`invoice_id`に対して複数の`invoice_token_hash`が存在することができるが、
		// クライアント側で待機後にアクセスするので、作成されたとしても数レコードの想定。
		// よって、`invoice_token_hash`のインデックスは張らない
		$sql = <<<SQL
			CREATE TABLE `{$this->table_name}` (
				`created_at`          timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`invoice_id`          varchar(191)  NOT NULL,
				`invoice_token_hash`  varchar(191)  NOT NULL,
				`expires_at`          timestamp     NOT NULL,
				`revoked_at`          timestamp         NULL DEFAULT NULL,
				PRIMARY KEY (`invoice_id`)
			) {$this->wpdb->get_charset_collate()};
		SQL;
		$this->wpdb->dbh->query( $sql );
	}

	public function down(): void {
		$this->wpdb->dbh->query( "DROP TABLE IF EXISTS `{$this->table_name}`;" );
	}
}
