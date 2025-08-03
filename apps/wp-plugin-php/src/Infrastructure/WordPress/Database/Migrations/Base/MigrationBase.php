<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Migrations\Base;

use Cornix\Serendipity\Core\Infrastructure\Database\MySQLiFactory;
use mysqli;
use RuntimeException;
use wpdb;

/**
 * 特定のバージョン、テーブルでのマイグレーションを行う基底クラス
 */
abstract class MigrationBase {
	private wpdb $wpdb;
	private mysqli $mysqli;
	private string $table_name;

	abstract public function up(): void;
	abstract public function down(): void;

	public function initialize( wpdb $wpdb, string $table_name ): void {
		$this->wpdb       = $wpdb;
		$this->mysqli     = ( new MySQLiFactory() )->create( $wpdb );
		$this->table_name = $table_name;
	}

	protected function tableName(): string {
		return $this->table_name;
	}
	protected function charset(): string {
		return $this->wpdb->get_charset_collate();
	}

	/**
	 * mysqliインスタンスを使用してSQLクエリを実行します。
	 */
	protected function query( string $sql ): void {
		$result = $this->mysqli->query( $sql );
		if ( true !== $result ) {
			throw new RuntimeException( "[4335F7E3] SQL query failed: {$sql}. " . $this->mysqli->error );
		}
	}

	/**
	 * wpdbインスタンスを使用してテーブルへデータを挿入します。
	 *
	 * @param string[]|string $format
	 */
	protected function insert( string $table, array $data, $format = null ): int {
		$result = $this->wpdb->insert( $table, $data, $format );
		if ( $result === false || 1 !== $result ) {
			throw new RuntimeException( "[777792DB] wpdb insert failed: {$this->wpdb->last_error}" );
		}
		return $result;
	}
}
