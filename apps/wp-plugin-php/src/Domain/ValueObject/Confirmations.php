<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\ValueObject;

use Baywall\Core\Domain\ValueObject\Interfaces\ValueObject;

/**
 * ブロックチェーンのトランザクションの確認数を表すクラス
 */
final class Confirmations implements ValueObject {

	private function __construct( int $confirmations_value ) {
		self::checkConfirmationsValue( $confirmations_value );
		$this->confirmations_value = $confirmations_value;
	}

	private int $confirmations_value;

	public static function from( int $confirmations_value ): self {
		return new self( $confirmations_value );
	}

	public function value(): int {
		return $this->confirmations_value;
	}

	public function __toString(): string {
		return (string) $this->confirmations_value;
	}

	public function equals( self $other ): bool {
		return $this->confirmations_value === $other->confirmations_value;
	}

	/**
	 * 確認数の値が正しい形式であることを確認する
	 */
	private static function checkConfirmationsValue( int $confirmations_value ): void {
		// confirmationsが数値の場合、1以上の整数であることを確認
		if ( $confirmations_value <= 0 ) {
			throw new \InvalidArgumentException( '[5DCC888A] Invalid confirmations value. Must be a positive integer. - ' . $confirmations_value );
		}
	}
}
