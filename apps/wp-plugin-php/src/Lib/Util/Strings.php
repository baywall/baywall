<?php
declare(strict_types=1);

namespace Baywall\Core\Lib\Util;

class Strings {
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
