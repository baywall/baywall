import { Symbol } from '@serendipity/lib-value-object';
import { getLegalCurrencyDecimals } from './getLegalCurrencyDecimals';

describe( '[Happy] getLegalCurrencyDecimals', () => {
	const legalCurrencyDataSet = [
		// 世界5大通貨
		{ symbol: 'USD', expectedDecimals: 2 },
		{ symbol: 'EUR', expectedDecimals: 2 },
		{ symbol: 'CNY', expectedDecimals: 2 },
		{ symbol: 'JPY', expectedDecimals: 0 },
		{ symbol: 'GBP', expectedDecimals: 2 },

		// 3桁の小数点を持つ通貨
		{ symbol: 'KWD', expectedDecimals: 3 },
	];
	/**
	 * 法定通貨の小数点以下桁数が正しく取得できること
	 */
	legalCurrencyDataSet.forEach( ( { symbol, expectedDecimals } ) => {
		it( `[9A2F3E8B] ${ symbol } => ${ expectedDecimals }`, () => {
			// ARRANGE
			const symbolObj = Symbol.from( symbol );

			// ACT
			const result = getLegalCurrencyDecimals( symbolObj );

			// ASSERT
			expect( result ).not.toBeNull();
			expect( result?.value ).toBe( expectedDecimals );
		} );
	} );

	const notLegalCurrencyDataSet = [ { symbol: 'BTC' }, { symbol: 'ETH' }, { symbol: 'XYZ' }, { symbol: 'INVALID' } ];
	/**
	 * 法定通貨でない場合はnullが返ること
	 */
	notLegalCurrencyDataSet.forEach( ( { symbol } ) => {
		it( `[4D7C1B5A] ${ symbol } => null`, () => {
			// ARRANGE
			const symbolObj = Symbol.from( symbol );

			// ACT
			const result = getLegalCurrencyDecimals( symbolObj );

			// ASSERT
			expect( result ).toBeNull();
		} );
	} );
} );
