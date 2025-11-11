<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Util\NamedPlaceholder;
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
	private NamedPlaceholder $named_placeholder;

	public function __construct( wpdb $wpdb ) {
		$this->wpdb              = $wpdb;
		$this->dbh               = new MyMySQLi( $wpdb->dbh );
		$this->named_placeholder = new NamedPlaceholder( $wpdb );
	}

	/**
	 * Named placeholder を使用して SQL クエリを構築します
	 * ※プレースホルダは、キーがコロンで始まる形式（例: `:key`）で指定してください。
	 *
	 * @param string              $query
	 * @param array<string,mixed> $args プレースホルダに対応する値の連想配列
	 */
	public function prepare( string $query, array $args ): string {
		return $this->named_placeholder->prepare( $query, $args );
	}

	/**
	 * wpdbインスタンスを使用してテーブルへデータを挿入します。
	 *
	 * @param string[]|string $format
	 */
	public function insert( string $table, array $data, $format = null ): int {
		$result = $this->wpdb->insert( $table, $data, $format );
		if ( $result === false ) {
			throw new RuntimeException( "[356E918B] wpdb insert failed: {$this->wpdb->last_error}" );
		}
		assert( is_int( $result ), "[221420F1] {$result}" );

		return $result;
	}


	/**
	 * wpdb->get_row
	 */
	public function getRow( string $query, string $output = OBJECT, int $y = 0 ) {
		$row = $this->wpdb->get_row( $query, $output, $y );
		if ( $row === null && ! empty( $this->wpdb->last_error ) ) {
			throw new RuntimeException( '[108B8388] Failed to get row. ' . $this->wpdb->last_error );
		}
		return $row;
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
