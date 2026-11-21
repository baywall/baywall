<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\ValueObject;

use Baywall\Core\Domain\ValueObject\Interfaces\ValueObject;

readonly class Price implements ValueObject {
	private function __construct(
		private readonly Amount $amount,
		private readonly Symbol $symbol
	) {}

	/**
	 * 金額の値を取得します。
	 */
	public function amount(): Amount {
		return $this->amount;
	}

	/** 通貨シンボル(`USD`, `ETH`等)を取得します。通貨記号(`$`等)ではありません。 */
	public function symbol(): Symbol {
		return $this->symbol;
	}

	public static function from( Amount $amount, Symbol $symbol ): self {
		return new self( $amount, $symbol );
	}

	/** 現在の Price オブジェクトが別の Price オブジェクトと等しいかどうかを判定します。 */
	public function equals( self $other ): bool {
		return $this->amount->equals( $other->amount ) && $this->symbol->equals( $other->symbol );
	}

	public function __toString(): string {
		return "{$this->amount->value()} {$this->symbol->value()}";
	}
}
