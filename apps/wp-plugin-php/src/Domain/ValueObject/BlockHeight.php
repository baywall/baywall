<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

/**
 * ブロック高を表すクラス
 */
class BlockHeight implements \Stringable {

	private function __construct( int $block_height_value ) {
		if ( $block_height_value < 0 ) {
			throw new \InvalidArgumentException( "[B0E977E6] Invalid block height value: {$block_height_value}" );
		}
		$this->block_height_value = $block_height_value;
	}

	private int $block_height_value;

	public static function from( int $block_height_value ): self {
		return new self( $block_height_value );
	}

	/** ブロック高を取得します。 */
	public function value(): int {
		return $this->block_height_value;
	}

	public function __toString(): string {
		return (string) $this->value();
	}
}
