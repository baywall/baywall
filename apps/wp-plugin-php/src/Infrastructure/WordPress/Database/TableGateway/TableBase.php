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

	protected function wpdb(): \wpdb {
		return $this->wpdb;
	}

	protected function tableName(): string {
		return $this->table_name;
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
	 * $wpdb->get_row() を安全に呼び出すためのヘルパー関数
	 *
	 * @param string $query
	 * @param string $output
	 * @param int    $y
	 * @return array|object|null|void
	 */
	protected function safeGetRow( string $query, string $output = OBJECT, int $y = 0 ) {
		$wpdb = $this->wpdb();
		$row  = $wpdb->get_row( $query, $output, $y );
		if ( $row === null && ! empty( $wpdb->last_error ) ) {
			throw new RuntimeException( '[E469E738] Failed to get row. ' . $wpdb->last_error );
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
		$wpdb    = $this->wpdb();
		$results = $wpdb->get_results( $query, $output );
		if ( ! empty( $wpdb->last_error ) ) {
			throw new RuntimeException( '[89353F21] Failed to get results. ' . $wpdb->last_error );
		}
		return $results;
	}
}
