import { Amount, Decimals } from '@serendipity/lib-value-object';
import { useIsDecimalPlacesError } from './useIsDecimalPlacesError';

describe( '[Happy] useIsDecimalPlacesError', () => {
	const dataSet: {
		inputAmount: Amount | null | undefined;
		maxDecimals: Decimals | null | undefined;
		expected: boolean;
	}[] = [
		// null/undefined cases - should return false (no error)
		{
			inputAmount: null,
			maxDecimals: null,
			expected: false,
		},
		{
			inputAmount: undefined,
			maxDecimals: undefined,
			expected: false,
		},
		{
			inputAmount: null,
			maxDecimals: undefined,
			expected: false,
		},
		{
			inputAmount: undefined,
			maxDecimals: null,
			expected: false,
		},
		{
			inputAmount: null,
			maxDecimals: Decimals.from( 2 ),
			expected: false,
		},
		{
			inputAmount: undefined,
			maxDecimals: Decimals.from( 2 ),
			expected: false,
		},
		{
			inputAmount: Amount.from( '100' ),
			maxDecimals: null,
			expected: false,
		},
		{
			inputAmount: Amount.from( '100' ),
			maxDecimals: undefined,
			expected: false,
		},
		// valid decimal places - should return false (no error)
		{
			inputAmount: Amount.from( '100.12' ),
			maxDecimals: Decimals.from( 2 ),
			expected: false,
		},
		// invalid decimal places - should return true (error)
		{
			inputAmount: Amount.from( '100.123' ),
			maxDecimals: Decimals.from( 2 ),
			expected: true,
		},
	];

	dataSet.forEach( ( { inputAmount, maxDecimals, expected } ) => {
		it( `[6A8C0E5D] ${ inputAmount }, ${ maxDecimals } => ${ expected }`, () => {
			// ACT
			const result = useIsDecimalPlacesError( inputAmount, maxDecimals );

			// ASSERT
			expect( result ).toBe( expected );
		} );
	} );
} );
