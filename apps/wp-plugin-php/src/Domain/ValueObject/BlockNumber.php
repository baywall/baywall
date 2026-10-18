<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\ValueObject;

use Baywall\Core\Domain\ValueObject\Interfaces\ValueObject;

/**
 * ブロック番号を表すクラス
 */
class BlockNumber implements ValueObject {

	private function __construct( int $block_number_value ) {
		if ( $block_number_value < 0 ) {
			throw new \InvalidArgumentException( "[60261E42] Invalid block number value: {$block_number_value}" );
		}
		$this->block_number_value = $block_number_value;
	}

	private readonly int $block_number_value;


	public static function fromInt( int $block_number_value ): self {
		return new self( $block_number_value );
	}
	public static function fromHex( Hex $hex ): self {
		return new self( $hex->intValue() );
	}

	public static function fromIntNullable( ?int $block_number ): ?self {
		return null === $block_number ? null : self::fromInt( $block_number );
	}

	/**
	 * 現在のブロック番号にブロック数（スパン）を加算した新しいインスタンスを取得します。
	 */
	public function add( BlockHeight $addend ): self {
		return new self( $this->block_number_value + $addend->value() );
	}

	/**
	 * 現在のブロック番号からブロック数（スパン）を減算した新しいインスタンスを取得します。
	 */
	public function sub( BlockHeight $subtrahend ): self {
		return new self( $this->block_number_value - $subtrahend->value() );
	}

	/**
	 * ブロック番号を比較します。
	 *
	 * $x > $y: $x->compare($y) > 0
	 * $x < $y: $x->compare($y) < 0
	 * $x == $y: $x->compare($y) == 0
	 */
	public function compare( self $other ): int {
		return $this->block_number_value <=> $other->block_number_value;
	}

	/**
	 * ブロック番号をHexオブジェクトで取得します。
	 */
	public function hex(): Hex {
		return Hex::from( '0x' . dechex( $this->block_number_value ) );
	}

	/**
	 * ブロック番号を整数で取得します。
	 */
	public function int(): int {
		return $this->block_number_value;
	}

	public function __toString(): string {
		return (string) $this->int();
	}

	public function equals( self $other ): bool {
		return $this->block_number_value === $other->block_number_value;
	}
}
