<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

/**
 * 小数点以下桁数を表す値オブジェクト
 */
class Decimals implements \Stringable {

	private function __construct( int $decimals_value ) {
		if ( $decimals_value < 0 ) {
			throw new \InvalidArgumentException( '[7CE0FCDE] Decimals must be a non-negative integer.' );
		}
		$this->decimals_value = $decimals_value;
	}
	private int $decimals_value;

	public function value(): int {
		return $this->decimals_value;
	}

	public static function from( int $decimals_value ): self {
		return new self( $decimals_value );
	}

	public function equals( self $other ): bool {
		return $this->decimals_value === $other->value();
	}

	public function __toString(): string {
		return (string) $this->decimals_value;
	}

	// public static function fromNullable( ?int $decimals_value ): ?self {
	// return $decimals_value === null ? null : new self( $decimals_value );
	// }
}
