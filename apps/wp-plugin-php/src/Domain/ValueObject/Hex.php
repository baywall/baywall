<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use InvalidArgumentException;

/**
 * 16進数の文字列を表す値オブジェクト
 */
class Hex implements \Stringable {

	private function __construct( string $hex_value ) {
		// 16進数の形式をチェック(大文字のA-Fは許容しない)
		if ( ! preg_match( '/^0x[0-9a-f]+$/', $hex_value ) ) {
			throw new \InvalidArgumentException( '[0FEF90DF] Invalid hex format for: ' . $hex_value );
		}
		$this->hex_value = $hex_value;
	}
	private string $hex_value;

	public function value(): string {
		return $this->hex_value;
	}

	public static function from( string $hex_value ): self {
		return new self( $hex_value );
	}

	public function equals( self $other ): bool {
		return $this->value() === $other->value();
	}

	public function __toString(): string {
		return $this->hex_value;
	}

	/**
	 * 指定した基数で文字列として値を取得します。
	 */
	public function toString( int $base = 16 ): string {
		if ( $base === 16 ) {
			return $this->hex_value;
		} elseif ( $base === 10 ) {
			$raw_hex_value = ltrim( $this->value(), '0x' );
			$result        = '0';
			$len           = strlen( $raw_hex_value );

			for ( $i = 0; $i < $len; $i++ ) {
				$digit  = strpos( '0123456789abcdef', $raw_hex_value[ $i ] );
				$power  = bcpow( '16', (string) ( $len - $i - 1 ) );
				$result = bcadd( $result, bcmul( (string) $digit, $power ) );
			}
			return $result;
		} else {
			throw new \InvalidArgumentException( '[BB835D38] Unsupported base for toString: ' . $base );
		}
	}

	/** int型の値で取得します */
	public function intValue(): int {
		$result = hexdec( $this->hex_value );
		if ( is_int( $result ) ) {
			return $result;
		}
		throw new \RuntimeException( '[F361A92C] Hex value is too large to convert to int: ' . $this->hex_value );
	}
}
