<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Util;

use InvalidArgumentException;
use wpdb;

class NamedPlaceholder {
	public function __construct( wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	private readonly wpdb $wpdb;

	/**
	 * Named placeholder を使用して SQL クエリを構築します
	 * ※プレースホルダは、キーがコロンで始まる形式（例: `:key`）で指定してください。
	 * ※クエリ中の `:[a-z0-9_]+` 形式のトークンはすべて `$args` で指定してください（未指定は `InvalidArgumentException`）。
	 *   SQL の文字列リテラル内に同形式の文字列（時刻 `12:30` 等）を書くことはできません。
	 *
	 * @param string              $query
	 * @param array<string,mixed> $args プレースホルダに対応する値の連想配列
	 */
	public function prepare( string $query, array $args ): string {

		// 例外の優先順位を検出順とするため、フォーマット検証は走査前に全キーに対して実施する
		foreach ( array_keys( $args ) as $key ) {
			$this->checkPlaceholderFormat( $key );
		}

		$used_keys = array();

		// クエリを1回だけ走査して置換する（置換結果はリテラル扱いのため、値に別プレースホルダ名を含んでも再置換されない）
		$result = preg_replace_callback(
			'/:[a-z0-9_]+/',
			function ( array $matches ) use ( $args, &$used_keys ): string {
				$token = $matches[0];
				if ( ! array_key_exists( $token, $args ) ) {
					throw new InvalidArgumentException( "[FB35D38A] Placeholder not found in args: {$token}" );
				}
				$used_keys[ $token ] = true;
				return $this->sanitizeValue( $args[ $token ] );
			},
			$query
		) ?? throw new InvalidArgumentException( '[3C52FC92] Failed to replace placeholders' );

		// 走査で1回も出現しなかったキーは例外（従来の契約を維持）
		foreach ( array_keys( $args ) as $key ) {
			if ( ! isset( $used_keys[ $key ] ) ) {
				throw new InvalidArgumentException( "[D3190731] Placeholder not found in query: {$key}" );
			}
		}

		return $result;
	}

	/** @param mixed $value */
	private function sanitizeValue( $value ): string {
		if ( is_null( $value ) ) {
			return 'NULL';
		} elseif ( is_int( $value ) || is_bool( $value ) ) {
			return $this->wpdb->prepare( '%d', $value ) ?? throw new InvalidArgumentException( '[689B634D] Failed to format value as %d.' );
		} elseif ( is_float( $value ) ) {
			return $this->wpdb->prepare( '%f', $value ) ?? throw new InvalidArgumentException( '[33F5FA53] Failed to format value as %f.' );
		} elseif ( is_string( $value ) ) {
			return $this->wpdb->prepare( '%s', $value ) ?? throw new InvalidArgumentException( '[C3C81D0F] Failed to format value as %s.' );
		} else {
			throw new InvalidArgumentException( '[D8496A28] Unsupported value type: ' . gettype( $value ) );
		}
	}

	/** プレースホルダのフォーマットに一致しているかどうかを返します */
	private function isPlaceholderFormat( string $key ): bool {
		return 1 === preg_match( '/^:[a-z0-9_]+$/', $key ); // 例: :key, :another_key など
	}

	/** プレースホルダのフォーマットが不正な場合に例外をスローします */
	private function checkPlaceholderFormat( string $key ): void {
		if ( ! $this->isPlaceholderFormat( $key ) ) {
			throw new InvalidArgumentException( "[B7A704BB] Invalid placeholder format: {$key}" );
		}
	}
}
