<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

class Rate {
	/**
	 * Rateインスタンスを生成します。
	 *
	 * @param SymbolPair $symbol_pair 通貨ペア
	 * @param Amount     $amount レートの数量
	 */
	private function __construct( SymbolPair $symbol_pair, Amount $amount ) {
		$this->symbol_pair = $symbol_pair;
		$this->amount      = $amount;
	}

	private SymbolPair $symbol_pair;
	private Amount $amount;

	public static function from( SymbolPair $symbol_pair, Amount $amount ): self {
		return new self( $symbol_pair, $amount );
	}

	public function symbolPair(): SymbolPair {
		return $this->symbol_pair;
	}

	public function amount(): Amount {
		return $this->amount;
	}

	/**
	 * レートを反転します。
	 * 例: ETH/USD (rate: 2000) → USD/ETH (rate: 0.0005)
	 *
	 * @param Decimals $max_decimals 割り切れない時の小数点以下桁数の最大精度
	 * @return Rate 反転されたレート
	 */
	public function invert( Decimals $max_decimals ): self {
		// 通貨ペアを逆転 (base ↔ quote)
		$inverted_symbol_pair = SymbolPair::from( $this->symbol_pair->quote(), $this->symbol_pair->base() );

		// レート値を逆数計算 (1 / rate)
		$one             = Amount::from( '1' );
		$inverted_amount = $one->div( $this->amount, $max_decimals );

		return self::from( $inverted_symbol_pair, $inverted_amount );
	}
}
