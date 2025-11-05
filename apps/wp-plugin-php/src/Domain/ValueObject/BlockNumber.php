<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use Cornix\Serendipity\Core\Infrastructure\Format\HexFormat;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use phpseclib\Math\BigInteger;

/**
 * ブロック番号を表すクラス
 */
class BlockNumber implements \Stringable {

	private function __construct( BigInteger $block_number ) {
		$this->block_number = $block_number;
	}

	private BigInteger $block_number;

	public static function fromInt( int $block_number ): self {
		return new self( new BigInteger( $block_number, 10 ) );
	}
	public static function fromHex( Hex $hex ): self {
		return new self( new BigInteger( $hex->value(), 16 ) );
	}

	public static function fromIntNullable( ?int $block_number ): ?self {
		return null === $block_number ? null : self::fromInt( $block_number );
	}

	/**
	 * 現在のブロック番号に引数の値を加算した新しいインスタンスを取得します。
	 */
	public function add( int $addend ): self {
		return new self( $this->block_number->add( new BigInteger( $addend, 10 ) ) );
	}

	/**
	 * 現在のブロック番号から引数の値を減算した新しいインスタンスを取得します。
	 */
	public function sub( int $subtrahend ): self {
		return new self( $this->block_number->subtract( new BigInteger( $subtrahend, 10 ) ) );
	}

	/**
	 * ブロック番号を比較します。
	 *
	 * $x > $y: $x->compare($y) > 0
	 * $x < $y: $x->compare($y) < 0
	 * $x == $y: $x->compare($y) == 0
	 */
	public function compare( self $other ): int {
		return $this->block_number->compare( $other->block_number );
	}

	/**
	 * ブロック番号を16進数表記で取得します。
	 */
	public function hex(): string {
		return HexFormat::toHex( $this->block_number );
	}

	/**
	 * ブロック番号を整数で取得します。
	 */
	public function int(): int {
		return HexFormat::toInt( $this->hex() );
	}

	public function __toString(): string {
		return (string) $this->int();
	}
}
