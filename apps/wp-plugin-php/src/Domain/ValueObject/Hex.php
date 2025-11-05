<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

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
}
