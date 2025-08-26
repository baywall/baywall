import { Amount } from './Amount';
import { Symbol } from './Symbol';

export class Price {
	public readonly amount: Amount;
	public readonly symbol: Symbol;

	private constructor( amount: Amount, symbol: Symbol ) {
		this.amount = amount;
		this.symbol = symbol;
	}

	public static from( amount: Amount, symbol: Symbol ): Price {
		return new Price( amount, symbol );
	}

	/**
	 * 価格を文字列で取得します
	 *
	 * - amount:100, symbol:USD => $100.00
	 * @param locales
	 */
	public toString( locales?: Intl.LocalesArgument ) {
		if ( this.symbol.isLegalCurrencySymbol( this.symbol.value ) ) {
			return legalCurrencyFormat( this.amount, this.symbol, locales );
		} else {
			return cryptoAssetsFormat( this.amount, this.symbol, locales );
		}
	}
}

/**
 * 法定通貨の価格を文字列で取得します
 * @param amount
 * @param symbol
 * @param locales
 */
const legalCurrencyFormat = ( amount: Amount, symbol: Symbol, locales?: Intl.LocalesArgument ): string => {
	return new Intl.NumberFormat( locales, {
		style: 'currency',
		currency: symbol.value,
	} ).format( Number( amount.value ) );
};
const cryptoAssetsFormat = ( amount: Amount, symbol: Symbol, locales?: Intl.LocalesArgument ): string => {
	const integerPart = amount.value.split( '.' )[ 0 ];
	const fractionalPart = amount.value.split( '.' )[ 1 ] || null;

	// 整数部をフォーマット
	const formattedInteger = new Intl.NumberFormat( locales ).format( BigInt( integerPart ) );

	// 数値と通貨記号の間は U+00a0 (NO-BREAK SPACE) を使用
	const separator = ' '; // eslint-disable-line no-irregular-whitespace

	// 価格を文字列で取得(数値と通貨記号の間は U+00a0 (NO-BREAK SPACE) を使用)
	if ( fractionalPart === null || fractionalPart.length === 0 ) {
		return `${ formattedInteger }${ separator }${ symbol.value }`;
	} else {
		// 小数点記号を取得
		const decimalSeparator = ( (): string => {
			const numberWithDecimal = new Intl.NumberFormat( locales ).format( 1.1 ); // 小数点以下を持つ数値をフォーマット（ここでは`1.1`を使用）
			const parts = numberWithDecimal.replace( /\d/g, '' ); // 数字（0-9）を除去して記号だけ抽出
			return parts;
		} )();

		return `${ formattedInteger }${ decimalSeparator }${ fractionalPart }${ separator }${ symbol.value }`;
	}
};
