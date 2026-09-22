<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\Util;

class Strings {
	public static function substr(
		string $string,
		int $start,
		?int $length = null,
		?string $encoding = null
	): string|false {
		return mb_substr( $string, $start, $length, $encoding );
	}


	/**
	 * 文字列内で最初に見つかった部分文字列の位置を返します。見つからなかった場合は false を返します。
	 */
	public static function strpos(
		string $haystack,
		string $needle,
		int $offset = 0
	): int|false {
		return mb_strpos( $haystack, $needle, $offset );
	}

	/**
	 * 文字列が指定した部分文字列を含んでいるかどうかを返します。
	 */
	public static function contains( string $haystack, string $needle ): bool {
		return str_contains( $haystack, $needle );
	}

	/**
	 * 文字列内の指定した文字列の出現位置をすべて検索します。(独自実装)
	 *
	 * @return int[]
	 */
	public static function all_strpos( string $haystack, string $needle ): array {
		$offset    = 0;
		$positions = array();
		while ( ( $pos = self::strpos( $haystack, $needle, $offset ) ) !== false ) {
			$positions[] = $pos;
			$offset      = $pos + 1;
		}
		return $positions;
	}


	/**
	 * @param string      $string
	 * @param string|null $encoding 省略またはnullの場合は内部エンコーディングを使用します。
	 */
	public static function strlen( string $string, ?string $encoding = null ): int {
		return mb_strlen( $string, $encoding );
	}

	public static function starts_with( string $string, string $prefix ): bool {
		return str_starts_with( $string, $prefix );
	}

	public static function ends_with( string $string, string $suffix ): bool {
		return str_ends_with( $string, $suffix );
	}
}
