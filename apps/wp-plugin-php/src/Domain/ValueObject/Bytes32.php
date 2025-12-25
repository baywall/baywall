<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Interfaces\ValueObject;

/**
 * bytes32型の値を表すクラス
 */
final class Bytes32 implements ValueObject {

	private function __construct( string $bytes32_value ) {
		self::checkValidBytes32Format( $bytes32_value );

		$this->bytes32_value = $bytes32_value;
	}
	private string $bytes32_value;

	public static function from( string $bytes32_value ): self {
		return new self( $bytes32_value );
	}

	public function __toString(): string {
		return $this->bytes32_value;
	}

	public function equals( self $other ): bool {
		return $this->bytes32_value === $other->bytes32_value;
	}

	public function bin(): string {
		return $this->hex()->bin();
	}

	public function hex(): Hex {
		return Hex::from( $this->bytes32_value );
	}

	private static function checkValidBytes32Format( string $bytes32_value ): void {
		if ( ! preg_match( '/^0x[0-9a-f]{64}$/', $bytes32_value, $matches ) ) {
			throw new \InvalidArgumentException( '[589860EA] Invalid bytes32 format. ' . $bytes32_value );
		}
	}

	/** ゼロのbytes32値を取得します */
	public static function zero(): self {
		return self::from( '0x0000000000000000000000000000000000000000000000000000000000000000' );
	}
}
