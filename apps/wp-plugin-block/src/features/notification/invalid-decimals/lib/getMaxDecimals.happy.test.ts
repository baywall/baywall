import { Decimals, NetworkCategoryId, Symbol } from '@serendipity/lib-value-object';
import { Token } from '../../../../value-object/Token';
import { getMaxDecimals } from './getMaxDecimals';

describe( '[Happy] getMaxDecimals', () => {
	/**
	 * 法定通貨の場合は法定通貨の小数点以下桁数が返ること
	 */
	describe( '[B32C3D5E] Legal Currency', () => {
		const legalCurrencyDataSet = [
			{ symbol: 'USD', expectedDecimals: 2 },
			{ symbol: 'JPY', expectedDecimals: 0 },
			{ symbol: 'KWD', expectedDecimals: 3 },
		];

		legalCurrencyDataSet.forEach( ( { symbol, expectedDecimals } ) => {
			it( `[8C545667] should return legal currency decimals for ${ symbol }`, () => {
				// ARRANGE
				const symbolObj = Symbol.from( symbol );
				const dic: Token[] = [];

				// ACT
				const result = getMaxDecimals( symbolObj, dic );

				// ASSERT
				expect( result ).not.toBeNull();
				expect( result?.value ).toBe( expectedDecimals );
			} );
		} );

		/**
		 * 法定通貨の場合、トークン辞書に同じシンボルが存在しても法定通貨の桁数が優先される
		 */
		it( '[F7E6ED99] should prioritize legal currency decimals even if token dictionary has the same symbol', () => {
			// ARRANGE
			const symbol = Symbol.from( 'USD' );
			const dic: Token[] = [
				Token.from( NetworkCategoryId.from( 1 ), symbol, Decimals.from( 18 ) ),
				Token.from( NetworkCategoryId.from( 1 ), symbol, Decimals.from( 6 ) ),
			];

			// ACT
			const result = getMaxDecimals( symbol, dic );

			// ASSERT
			expect( result ).not.toBeNull();
			expect( result?.value ).toBe( 2 ); // 法定通貨USDの桁数
		} );
	} );

	/**
	 * トークンの場合は辞書から最大小数点以下桁数が返ること
	 */
	describe( '[67AA5BCB] Token', () => {
		/**
		 * 単一のトークンの場合、そのトークンの小数点以下桁数が返る
		 */
		it( '[BDA9BED7] should return token decimals for a single token', () => {
			// ARRANGE
			const symbol = Symbol.from( 'ETH' );
			const dic: Token[] = [ Token.from( NetworkCategoryId.from( 1 ), symbol, Decimals.from( 18 ) ) ];

			// ACT
			const result = getMaxDecimals( symbol, dic );

			// ASSERT
			expect( result ).not.toBeNull();
			expect( result?.value ).toBe( 18 );
		} );

		/**
		 * 複数のトークンがある場合、最大の小数点以下桁数が返る
		 */
		it( '[CC058E8E] should return maximum decimals when multiple tokens exist', () => {
			// ARRANGE
			const symbol = Symbol.from( 'USDT' );
			const dic: Token[] = [
				Token.from( NetworkCategoryId.from( 1 ), symbol, Decimals.from( 6 ) ),
				Token.from( NetworkCategoryId.from( 1 ), symbol, Decimals.from( 18 ) ),
				Token.from( NetworkCategoryId.from( 1 ), symbol, Decimals.from( 6 ) ),
			];

			// ACT
			const result = getMaxDecimals( symbol, dic );

			// ASSERT
			expect( result ).not.toBeNull();
			expect( result?.value ).toBe( 18 );
		} );

		/**
		 * 辞書に同じシンボルが複数あり、decimalsが同じ場合
		 */
		it( '[2E234E4B] should return decimals when multiple tokens have the same decimals', () => {
			// ARRANGE
			const symbol = Symbol.from( 'USDC' );
			const dic: Token[] = [
				Token.from( NetworkCategoryId.from( 1 ), symbol, Decimals.from( 6 ) ),
				Token.from( NetworkCategoryId.from( 1 ), symbol, Decimals.from( 6 ) ),
			];

			// ACT
			const result = getMaxDecimals( symbol, dic );

			// ASSERT
			expect( result ).not.toBeNull();
			expect( result?.value ).toBe( 6 );
		} );
	} );

	/**
	 * シンボルが辞書に存在しない場合はnullが返ること
	 */
	describe( '[1EA34089] Not Found', () => {
		/**
		 * 辞書が空の場合、法定通貨でないシンボルはnullが返る
		 */
		it( '[D1BB7760] should return null for non-legal currency symbol when dictionary is empty', () => {
			// ARRANGE
			const symbol = Symbol.from( 'UNKNOWN' );
			const dic: Token[] = [];

			// ACT
			const result = getMaxDecimals( symbol, dic );

			// ASSERT
			expect( result ).toBeNull();
		} );

		/**
		 * 辞書に異なるシンボルしかない場合、nullが返る
		 */
		it( '[27C1443E] should return null when dictionary only contains different symbols', () => {
			// ARRANGE
			const symbol = Symbol.from( 'BTC' );
			const dic: Token[] = [
				Token.from( NetworkCategoryId.from( 1 ), Symbol.from( 'ETH' ), Decimals.from( 18 ) ),
				Token.from( NetworkCategoryId.from( 1 ), Symbol.from( 'MATIC' ), Decimals.from( 18 ) ),
			];

			// ACT
			const result = getMaxDecimals( symbol, dic );

			// ASSERT
			expect( result ).toBeNull();
		} );
	} );

	/**
	 * エッジケース
	 */
	describe( '[1BF331DA] Edge Cases', () => {
		/**
		 * 辞書に0個のdecimalsを持つトークンがある場合
		 */
		it( '[C53C919A] should return 0 when token has 0 decimals', () => {
			// ARRANGE
			const symbol = Symbol.from( 'TOKEN' );
			const dic: Token[] = [ Token.from( NetworkCategoryId.from( 1 ), symbol, Decimals.from( 0 ) ) ];

			// ACT
			const result = getMaxDecimals( symbol, dic );

			// ASSERT
			expect( result ).not.toBeNull();
			expect( result?.value ).toBe( 0 );
		} );

		/**
		 * 辞書に複数のネットワークで同じシンボルがあり、decimalsが異なる場合
		 */
		it( '[6CB189FF] should return maximum decimals when same symbol exists across multiple networks', () => {
			// ARRANGE
			const symbol = Symbol.from( 'WETH' );
			const dic: Token[] = [
				Token.from( NetworkCategoryId.from( 1 ), symbol, Decimals.from( 18 ) ),
				Token.from( NetworkCategoryId.from( 1 ), symbol, Decimals.from( 18 ) ),
				Token.from( NetworkCategoryId.from( 1 ), symbol, Decimals.from( 18 ) ),
			];

			// ACT
			const result = getMaxDecimals( symbol, dic );

			// ASSERT
			expect( result ).not.toBeNull();
			expect( result?.value ).toBe( 18 );
		} );
	} );
} );
