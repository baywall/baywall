<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database;

use mysqli;
use mysqli_result;
use RuntimeException;
use wpdb;

/**
 * wpdbクラスのような振る舞いをする独自クラス
 */
class MyWpdb {
	private wpdb $wpdb;
	public MyMySQLi $dbh;

	public function __construct( wpdb $wpdb ) {
		$this->wpdb = $wpdb;
		$this->dbh  = new MyMySQLi( $wpdb->dbh );
	}

	/**
	 * wpdbインスタンスを使用してテーブルへデータを挿入します。
	 *
	 * @param string[]|string $format
	 */
	public function insert( string $table, array $data, $format = null ): int {
		$result = $this->wpdb->insert( $table, $data, $format );
		if ( ! is_int( $result ) ) {
			throw new RuntimeException( "[356E918B] wpdb insert failed: {$this->wpdb->last_error}" );
		}
		return $result;
	}

	/**
	 *
	 * @return string `DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci`のような文字列
	 */
	public function get_charset_collate(): string {
		return $this->wpdb->get_charset_collate();
	}
}

/**
 * mysqliクラスのような振る舞いをする独自クラス
 */
class MyMySQLi {
	private mysqli $mysqli;

	public function __construct( mysqli $mysqli ) {
		$this->mysqli = $mysqli;
	}

	/**
	 * Performs a query on the database
	 *
	 * @param string $query
	 * @param int    $result_mode
	 * @return mysqli_result|bool
	 * @throws RuntimeException
	 */
	public function query( string $query, int $result_mode = MYSQLI_STORE_RESULT ) {
		$result = $this->mysqli->query( $query, $result_mode );
		if ( $result === false ) {
			throw new RuntimeException( "[2ACD4F6A] SQL query failed: {$query}. " . $this->mysqli->error );
		}
		assert( $result instanceof mysqli_result || $result === true, '[2D4F8743] Invalid query result type.' );
		return $result;
	}
}
