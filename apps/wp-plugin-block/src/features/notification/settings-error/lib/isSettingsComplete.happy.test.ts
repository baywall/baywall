import { isSettingsComplete } from './isSettingsComplete';
import { BlockInitDataType } from '../../../../query/useBlockInitDataQuery';

describe( '[Happy] isSettingsComplete', () => {
	/**
	 * モックのBlockInitDataTypeを作成
	 * @param sellableNetworkCategoriesLength
	 */
	const createMockBlockInitData = ( sellableNetworkCategoriesLength: number ): BlockInitDataType => {
		return {
			sellableNetworkCategories: Array( sellableNetworkCategoriesLength ).fill( {} ),
		} as BlockInitDataType;
	};

	const dataSet = [
		{ description: 'data is undefined', data: undefined, expectedResult: undefined },
		{
			description: 'sellableNetworkCategories is empty',
			data: createMockBlockInitData( 0 ),
			expectedResult: false,
		},
		{
			description: 'sellableNetworkCategories has one item',
			data: createMockBlockInitData( 1 ),
			expectedResult: true,
		},
		{
			description: 'sellableNetworkCategories has multiple items',
			data: createMockBlockInitData( 3 ),
			expectedResult: true,
		},
	];

	dataSet.forEach( ( { description, data, expectedResult } ) => {
		it( `[E2310B09] should return ${ expectedResult } when ${ description }`, () => {
			// ACT
			const result = isSettingsComplete( data );

			// ASSERT
			expect( result ).toBe( expectedResult );
		} );
	} );
} );
