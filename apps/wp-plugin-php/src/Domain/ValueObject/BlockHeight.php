<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\ValueObject;

use Baywall\Core\Domain\ValueObject\Interfaces\ValueObject;

/**
 * 位置間の差分（ブロック数）を表すクラス
 *
 * ブロック上の位置（ブロック番号）はBlockNumberで表す。
 * BlockNumber::add() / sub()の引数のような「位置 ± スパン」の演算に用いる。
 */
class BlockHeight implements ValueObject {

	private function __construct( int $block_height_value ) {
		if ( $block_height_value < 0 ) {
			throw new \InvalidArgumentException( "[B0E977E6] Invalid block height value: {$block_height_value}" );
		}
		$this->block_height_value = $block_height_value;
	}

	private readonly int $block_height_value;

	public static function from( int $block_height_value ): self {
		return new self( $block_height_value );
	}

	/** ブロック高（ブロック数）を取得します。 */
	public function value(): int {
		return $this->block_height_value;
	}

	public function equals( self $other ): bool {
		return $this->block_height_value === $other->block_height_value;
	}

	public function __toString(): string {
		return (string) $this->value();
	}
}
