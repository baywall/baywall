<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Interfaces\ValueObject;

/** 署名データ */
class Signature implements ValueObject {

	private Bytes $value;

	private function __construct( Bytes $value ) {
		$this->checkValue( $value );

		$this->value = $value;
	}

	public static function from( string $signature_value ): self {
		return new self( Bytes::fromHex( Hex::from( $signature_value ) ) );
	}

	public function bin(): string {
		return $this->value->bin();
	}

	public function hex(): Hex {
		return $this->value->hex();
	}

	public function __toString(): string {
		return $this->value->__toString();
	}

	public function equals( self $other ): bool {
		return $this->value->equals( $other->value );
	}

	private function checkValue( Bytes $value ): void {
		if ( strlen( $value->bin() ) !== 65 ) {
			throw new \InvalidArgumentException( '[DF154E53] Invalid signature value. ' . $value );
		}
	}
}
