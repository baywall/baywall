<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database;

use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Util\NamedPlaceholder;
use RuntimeException;
use wpdb;

/**
 * wpdbクラスのような振る舞いをする独自クラス
 */
class MyWpdb {
	private wpdb $wpdb;
	private NamedPlaceholder $named_placeholder;
	public string $posts;

	public function __construct( wpdb $wpdb ) {
		$this->wpdb              = $wpdb;
		$this->named_placeholder = new NamedPlaceholder( $wpdb );
		$this->posts             = $wpdb->posts;
	}

	/**
	 * wpdb->prepare
	 *
	 * @deprecated Use named_prepare instead.
	 * @param string $query
	 * @param mixed  ...$args
	 * @return string|null
	 */
	public function prepare( string $query, ...$args ) {
		// wpdb->prepareは、WordPress6.2以降で`%i`(テーブル名やフィールド名)が使用可能。
		// 本プラグインのサポートバージョンは6.6以降(2026年4月26日現在)のため、`%i`のチェックは不要。
		// @see https://developer.wordpress.org/reference/classes/wpdb/prepare/#changelog
		return $this->wpdb->prepare( $query, ...$args );
	}

	/**
	 * Named placeholder を使用して SQL クエリを構築します
	 * ※ prepareと異なり、`null`は`NULL`として扱われます。（prepareは`null`を空文字として扱う）
	 * ※プレースホルダは、キーがコロンで始まる形式（例: `:key`）で指定してください。
	 *
	 * @param string              $query
	 * @param array<string,mixed> $args プレースホルダに対応する値の連想配列
	 */
	public function named_prepare( string $query, array $args ): string {
		return $this->named_placeholder->prepare( $query, $args );
	}

	/**
	 * wpdb->query
	 *
	 * @param string $query
	 * @return int|true
	 */
	public function query( string $query ) {
		$result = $this->wpdb->query( $query );
		if ( $result === false ) {
			throw new RuntimeException( "[D76677B0] SQL query failed: {$query}. " . $this->wpdb->last_error );
		}
		assert( $result === true || is_int( $result ), "[4E471767] {$result}" );
		return $result;
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
	 * wpdb->update
	 *
	 * @param string          $table
	 * @param array           $data
	 * @param array           $where
	 * @param string[]|string $format
	 * @param string[]|string $where_format
	 * @return int
	 */
	public function update( string $table, array $data, array $where, $format = null, $where_format = null ): int {
		$result = $this->wpdb->update( $table, $data, $where, $format, $where_format );
		if ( $result === false ) {
			throw new RuntimeException( "[3AC6E13E] wpdb update failed: {$this->wpdb->last_error}" );
		}
		assert( is_int( $result ), "[6131415E] {$result}" );

		return $result;
	}

	/**
	 * wpdb->delete
	 */
	public function delete( string $table, array $where, $where_format = null ): int {
		$result = $this->wpdb->delete( $table, $where, $where_format );
		if ( $result === false ) {
			throw new RuntimeException( "[64BB03B5] wpdb delete failed: {$this->wpdb->last_error}" );
		}
		assert( is_int( $result ), "[3F45CD57] {$result}" );

		return $result;
	}

	/**
	 * wpdb->get_row
	 */
	public function get_row( string $query, string $output = OBJECT, int $y = 0 ) {
		$row = $this->wpdb->get_row( $query, $output, $y );
		if ( $row === null && ! empty( $this->wpdb->last_error ) ) {
			throw new RuntimeException( '[108B8388] Failed to get row. ' . $this->wpdb->last_error );
		}
		return $row;
	}

	/**
	 * wpdb->get_results
	 *
	 * @return array|object|null
	 */
	public function get_results( string $query, string $output = OBJECT ) {
		$results = $this->wpdb->get_results( $query, $output );
		if ( ! empty( $this->wpdb->last_error ) ) {
			throw new RuntimeException( '[FB1C88B8] Failed to get results. ' . $this->wpdb->last_error );
		}
		return $results;
	}

	/**
	 * wpdb->get_var
	 *
	 * @return string|null Database query result (as string), or null on failure.
	 */
	public function get_var( string $query, int $x = 0, int $y = 0 ) {
		$var = $this->wpdb->get_var( $query, $x, $y );
		if ( $var === null && ! empty( $this->wpdb->last_error ) ) {
			throw new RuntimeException( '[B0B02673] Failed to get var. ' . $this->wpdb->last_error );
		}
		return $var;
	}

	/**
	 *
	 * @return string `DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci`のような文字列
	 */
	public function get_charset_collate(): string {
		return $this->wpdb->get_charset_collate();
	}
}
