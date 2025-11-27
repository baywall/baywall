<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Interfaces\ValueObject;

/** 署名のためのメッセージ */
class SigningMessage implements ValueObject {

	private function __construct( string $signing_message_value ) {
		$this->signing_message_value = $signing_message_value;
	}
	public static function from( string $signing_message_value ): self {
		return new self( $signing_message_value );
	}

	private string $signing_message_value;

	public function value(): string {
		return $this->signing_message_value;
	}

	public function __toString(): string {
		return $this->signing_message_value;
	}
}
