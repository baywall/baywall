<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use DivisionByZeroError;
use InvalidArgumentException;

/**
 * 数量等を表すクラス
 */
final class Amount implements \Stringable {

	private function __construct( string $amount_text ) {
		if ( false !== strpos( $amount_text, '.' ) ) {
			// 小数点以下の末尾が0の場合は削除
			$amount_text = rtrim( $amount_text, '0' );
			// 小数点以下がなくなった場合は小数点も削除
			$amount_text = rtrim( $amount_text, '.' );
		}

		self::checkAmountText( $amount_text );
		$this->amount_text = $amount_text;
	}

	/** 値を10進数の文字列で保持 */
	private string $amount_text;

	/** 10進数の文字列からインスタンスを作成します。 */
	public static function from( string $amount_text ): self {
		return new self( $amount_text );
	}

	/** 16進数の文字列からインスタンスを作成します。 */
	public static function fromHex( Hex $hex ): self {
		return new self( $hex->toString( 10 ) );
	}

	public static function fromNullable( ?string $amount_text ): ?self {
		return null !== $amount_text ? new self( $amount_text ) : null;
	}

	/**
	 * 基本単位からAmountを生成します。
	 *
	 * 例: 1000000000を10桁の小数点以下を持つAmountに変換する場合、1000000000を10^10で割る。
	 *
	 * @param string $base_unit 基本単位の値
	 * @return self 基本単位から生成されたAmountインスタンス
	 */
	public static function fromBaseUnitAndDecimals( string $base_unit, Decimals $decimals ): self {
		// 基本単位から小数点以下の桁数を考慮してAmountを生成
		$multiplier = (string) ( 10 ** $decimals->value() );
		return new self( bcdiv( $base_unit, $multiplier, $decimals->value() ) );
	}

	public function isZero(): bool {
		return '0' === $this->amount_text;
	}

	public function isNegative(): bool {
		return str_starts_with( $this->amount_text, '-' );
	}

	/** 小数点以下の桁数を取得します */
	public function decimals(): Decimals {
		return Decimals::from( strlen( explode( '.', $this->amount_text )[1] ?? '' ) );
	}

	public function equals( self $other ): bool {
		return $this->amount_text === $other->amount_text;
	}

	public function value(): string {
		return $this->amount_text;
	}

	public function __toString() {
		return $this->value();
	}

	/**
	 * 指定した小数点桁数に切り捨てます。
	 *
	 * @param Decimals $decimals 小数点以下の桁数
	 * @return self 切り捨てた結果の新しいAmountインスタンス
	 */
	public function floor( Decimals $decimals ): self {
		return new self( bcdiv( $this->amount_text, '1', $decimals->value() ) );
	}

	public function add( self $other ): self {
		return new self( bcadd( $this->amount_text, $other->amount_text, max( $this->decimals()->value(), $other->decimals()->value() ) ) );
	}

	public function sub( self $other ): self {
		return new self( bcsub( $this->amount_text, $other->amount_text, max( $this->decimals()->value(), $other->decimals()->value() ) ) );
	}

	public function mul( self $other ): self {
		return new self( bcmul( $this->amount_text, $other->amount_text, ( $this->decimals()->value() + $other->decimals()->value() ) ) );
	}

	/**
	 *
	 * @param Amount        $other
	 * @param null|Decimals $accuracy_decimals 最大精度。割り切れない場合は、指定した精度で切り捨て。
	 * @throws DivisionByZeroError
	 */
	public function div( self $other, Decimals $accuracy_decimals ): self {
		if ( $other->isZero() ) {
			throw new DivisionByZeroError( "[B3F404A6] The expression is invalid: {$this->amount_text} / {$other->amount_text}" );
		}

		return new self( bcdiv( $this->amount_text, $other->amount_text, $accuracy_decimals->value() ) );
	}

	private static function checkAmountText( string $amount_text ): void {
		// 数値の形式をチェック
		if ( ! preg_match( '/^-?(?:0|[1-9]\d*)(\.\d+)?$/', $amount_text ) ) {
			throw new \InvalidArgumentException( '[275B6F0E] Invalid amount text: ' . $amount_text );
		}
	}
}
