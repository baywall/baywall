<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\TableGateway;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Util\NamedPlaceholder;
use RuntimeException;

abstract class TableBase {
	public function __construct( \wpdb $wpdb, string $table_name ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $table_name;
	}
	private \wpdb $wpdb;
	private string $table_name;

	protected function tableName(): string {
		return $this->table_name;
	}

	/**
	 * $wpdb->prepare() を呼び出します
	 *
	 * @param string $query
	 * @param array  ...$args
	 */
	protected function prepare( string $query, ...$args ): string {
		return $this->wpdb->prepare( $query, ...$args );
	}

	/**
	 * Named placeholder を使用して SQL クエリを構築します
	 * ※プレースホルダは、キーがコロンで始まる形式（例: `:key`）で指定してください。
	 *
	 * @param string               $query
	 * @param array<string,string> $args プレースホルダに対応する値の連想配列
	 */
	protected function namedPrepare( string $query, array $args ): string {
		return ( new NamedPlaceholder( $this->wpdb ) )->prepare( $query, $args );
	}

	/**
	 * $wpdb->query() を安全に呼び出すためのヘルパー関数
	 *
	 * @param string $query
	 * @return int
	 */
	protected function safeQuery( string $query ): int {
		$result = $this->wpdb->query( $query );
		if ( false === $result ) {
			throw new RuntimeException( '[BF06339E] Failed to execute query. ' . $this->wpdb->last_error );
		}
		return $result;
	}

	/**
	 * $wpdb->get_var() を安全に呼び出すためのヘルパー関数
	 *
	 * @param string $query
	 * @param int    $x
	 * @param int    $y
	 * @return string|null
	 */
	protected function safeGetVar( string $query, int $x = 0, int $y = 0 ) {
		$result = $this->wpdb->get_var( $query, $x, $y );
		if ( $result === null && ! empty( $this->wpdb->last_error ) ) {
			throw new RuntimeException( '[10D69A0C] Failed to get variable. ' . $this->wpdb->last_error );
		}
		return $result;
	}

	/**
	 * $wpdb->get_row() を安全に呼び出すためのヘルパー関数
	 *
	 * @param string $query
	 * @param string $output
	 * @param int    $y
	 * @return array|object|null|void
	 */
	protected function safeGetRow( string $query, string $output = OBJECT, int $y = 0 ) {
		$row = $this->wpdb->get_row( $query, $output, $y );
		if ( $row === null && ! empty( $this->wpdb->last_error ) ) {
			throw new RuntimeException( '[E469E738] Failed to get row. ' . $this->wpdb->last_error );
		}
		return $row;
	}


	/**
	 * $wpdb->get_results() を安全に呼び出すためのヘルパー関数
	 *
	 * @param string $query
	 * @return array|object|null
	 */
	protected function safeGetResults( string $query, string $output = OBJECT ) {
		$results = $this->wpdb->get_results( $query, $output );
		if ( ! empty( $this->wpdb->last_error ) ) {
			throw new RuntimeException( '[89353F21] Failed to get results. ' . $this->wpdb->last_error );
		}
		return $results;
	}

	/**
	 * $wpdb->insert() を安全に呼び出すためのヘルパー関数
	 *
	 * @param string               $table
	 * @param array                $data
	 * @param string[]|string|null $format
	 * @return int
	 */
	protected function safeInsert( string $table, array $data, $format = null ): int {
		$result = $this->wpdb->insert( $table, $data, $format );
		if ( false === $result ) {
			throw new RuntimeException( '[6B5F6CA6] Failed to insert data. ' . $this->wpdb->last_error );
		}
		return $result;
	}

	/**
	 * $wpdb->update() を安全に呼び出すためのヘルパー関数
	 *
	 * @param string               $table
	 * @param array                $where
	 * @param string[]|string|null $where_format
	 * @return int
	 */
	protected function safeDelete( string $table, array $where, $where_format = null ): int {
		$result = $this->wpdb->delete( $table, $where, $where_format );
		if ( false === $result ) {
			throw new RuntimeException( '[7E3D8D05] Failed to delete data. ' . $this->wpdb->last_error );
		}
		return $result;
	}
}
