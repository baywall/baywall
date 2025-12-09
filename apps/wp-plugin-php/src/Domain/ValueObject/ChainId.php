<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Interfaces\ValueObject;

/**
 * チェーンIDを表す値オブジェクト
 */
class ChainId implements ValueObject {

	private function __construct( int $chain_id_value ) {
		if ( $chain_id_value <= 0 ) {
			throw new \InvalidArgumentException( '[44CF8BCC] Chain ID must be a positive integer.' );
		}
		$this->chain_id_value = $chain_id_value;
	}
	private int $chain_id_value;

	public function value(): int {
		return $this->chain_id_value;
	}

	public function equals( self $other ): bool {
		return $this->chain_id_value === $other->chain_id_value;
	}

	public function __toString(): string {
		return (string) $this->chain_id_value;
	}

	public static function from( int $chain_id_value ): self {
		return new self( $chain_id_value );
	}

	public static function fromNullableValue( ?int $chain_id_value ): ?self {
		return $chain_id_value === null ? null : self::from( $chain_id_value );
	}
}
