<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations;

use Baywall\Core\Infrastructure\WordPress\Database\Migration\Migrations\Base\MigrationBase;
use Baywall\Core\Infrastructure\WordPress\Database\MyWpdb;
use Baywall\Core\Infrastructure\WordPress\Database\TableNameProvider;

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
		// `private_key`はウォレットの秘密鍵をBase64エンコードしたものを保存(そのままコピペでウォレットに登録されないようにしているだけ)
		// `private_key_enc_type`は`private_key`の保存形式を表す

		// 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->table_name}` (
				`created_at`           bigint unsigned NOT NULL,
				`updated_at`           bigint unsigned NOT NULL,
				`address`              varchar(191)   NOT NULL,
				`private_key`          varchar(191)   NOT NULL,
				`private_key_enc_type` int            NOT NULL,
				-- @see Infrastructure/WordPress/Database/ValueObject/PrivateKeyEncryptionType（`enc_type = 2`の許可は暗号化を実装する別issueで行う）
				CONSTRAINT `chk_{$this->table_name}_private_key` CHECK (`private_key_enc_type` = 1 AND CONVERT(`private_key` USING utf8mb4) COLLATE utf8mb4_bin REGEXP '^[0-9A-Za-z+/]{88}$'),
				CONSTRAINT `chk_{$this->table_name}_address` CHECK (CONVERT(`address` USING utf8mb4) COLLATE utf8mb4_bin REGEXP '^0x[0-9a-f]{40}$'),
				PRIMARY KEY (`address`)
			) {$this->wpdb->get_charset_collate()};
		SQL;
		$this->wpdb->query( $sql );
	}

	public function down(): void {
		$this->wpdb->query( "DROP TABLE IF EXISTS `{$this->table_name}`;" );
	}
}
