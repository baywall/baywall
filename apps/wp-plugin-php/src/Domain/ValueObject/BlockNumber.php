<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Interfaces\ValueObject;

/**
 * ブロック番号を表すクラス
 */
class BlockNumber implements ValueObject {

	private function __construct( int $block_number_value ) {
		$this->block_number_value = $block_number_value;
	}

	private int $block_number_value;

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
	 * 現在のブロック番号に引数の値を加算した新しいインスタンスを取得します。
	 * TODO: 引数の型をBlockHeightに変更
	 */
	public function add( int $addend ): self {
		return new self( $this->block_number_value + $addend );
	}

	/**
	 * 現在のブロック番号から引数の値を減算した新しいインスタンスを取得します。
	 *
	 * TODO: 引数の型をBlockHeightに変更
	 */
	public function sub( int $subtrahend ): self {
		return new self( $this->block_number_value - $subtrahend );
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
	 * ブロック番号を16進数表記で取得します。
	 */
	public function hex(): string {
		return Hex::from( '0x' . dechex( $this->block_number_value ) )->value();
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
}
