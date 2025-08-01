<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

/** 署名データ */
class Signature {

	private function __construct( string $signature ) {
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
}
