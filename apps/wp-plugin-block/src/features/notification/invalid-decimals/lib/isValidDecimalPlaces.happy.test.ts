import { Amount, Decimals } from '@serendipity/lib-value-object';
import { isValidDecimalPlaces } from './isValidDecimalPlaces';

describe( '[Happy] isValidDecimalPlaces', () => {
	const dataSet = [
		// 小数点以下桁数が最大桁数以下の場合
		{ amountValue: '123.45', maxDecimalPlaces: 2, expected: true },
		{ amountValue: '123.4', maxDecimalPlaces: 2, expected: true },
		{ amountValue: '123', maxDecimalPlaces: 2, expected: true },
		{ amountValue: '100', maxDecimalPlaces: 0, expected: true },
		{ amountValue: '0.123', maxDecimalPlaces: 5, expected: true },

		// 小数点以下桁数が最大桁数を超えている場合
		{ amountValue: '123.456', maxDecimalPlaces: 2, expected: false },
		{ amountValue: '100.5', maxDecimalPlaces: 0, expected: false },
		{ amountValue: '0.12', maxDecimalPlaces: 1, expected: false },

		// 境界値テスト
		{ amountValue: '0.123456789012345678', maxDecimalPlaces: 18, expected: true },
		{ amountValue: '0.1234567890123456789', maxDecimalPlaces: 18, expected: false },
		{ amountValue: '0', maxDecimalPlaces: 2, expected: true },
		{ amountValue: '123.00', maxDecimalPlaces: 2, expected: true },
	];
	dataSet.forEach( ( { amountValue, maxDecimalPlaces, expected } ) => {
		it( `[5C1BE1A7] amount: ${ amountValue }, maxDecimalPlaces: ${ maxDecimalPlaces } => ${ expected }`, () => {
			// ARRANGE
			const amount = Amount.from( amountValue );
			const maxDecimalPlacesObj = Decimals.from( maxDecimalPlaces );

			// ACT
			const result = isValidDecimalPlaces( amount, maxDecimalPlacesObj );

			// ASSERT
			expect( result ).toBe( expected );
		} );
	} );
} );
