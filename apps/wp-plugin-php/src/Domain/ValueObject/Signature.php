<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Interfaces\ValueObject;

/** 署名データ */
class Signature implements ValueObject {

	private function __construct( string $signature ) {
		$this->checkFormat( $signature );

		$this->signature_value = $signature;
	}

	private string $signature_value;

	public static function from( string $signature ): self {
		return new self( $signature );
	}

	public function value(): string {
		return $this->signature_value;
	}

	public function __toString(): string {
		return $this->signature_value;
	}

	private function checkFormat( string $signature ): void {
		if ( ! preg_match( '/^0x[0-9a-f]{130}$/', $signature ) ) {
			throw new \InvalidArgumentException( "[DF154E53] Invalid signature format. '{$signature}" );
		}
	}
}
