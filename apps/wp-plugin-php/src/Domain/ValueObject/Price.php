<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

class Price {
	private function __construct( Amount $amount, Symbol $symbol ) {
		$this->amount = $amount;
		$this->symbol = $symbol;
	}

	private Amount $amount;
	private Symbol $symbol;

	/**
	 * 金額の数量を取得します。
	 */
	public function amount(): Amount {
		return $this->amount;
	}

	/** 通貨記号(`USD`, `ETH`等)を取得します。記号(`$`等)ではない。 */
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
