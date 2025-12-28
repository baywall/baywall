<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Interfaces\ValueObject;

/**
 * bytes32型の値を表すクラス
 */
final class Bytes32 implements ValueObject {

	private Bytes $value;

	private function __construct( Bytes $value ) {
		self::checkValue( $value );

		$this->value = $value;
	}

	public static function fromHex( Hex $bytes32_hex ): self {
		return new self( Bytes::fromHex( $bytes32_hex ) );
	}

	public function __toString(): string {
		return $this->value->__toString();
	}

	public function equals( self $other ): bool {
		return $this->value->equals( $other->value );
	}

	public function bin(): string {
		return $this->value->bin();
	}

	public function hex(): Hex {
		return $this->value->hex();
	}

	private static function checkValue( Bytes $value ): void {
		if ( strlen( $value->bin() ) !== 32 ) {
			throw new \InvalidArgumentException( '[589860EA] Invalid bytes32 value. ' . $value );
		}
	}

	/** ゼロのbytes32値を取得します */
	public static function zero(): self {
		return self::fromHex( Hex::from( '0x0000000000000000000000000000000000000000000000000000000000000000' ) );
	}
}
