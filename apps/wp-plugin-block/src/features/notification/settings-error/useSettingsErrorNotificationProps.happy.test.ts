import { describe, expect, it } from '@jest/globals';
import { NetworkCategoryId, Symbol } from '@serendipity/lib-value-object';
import { BlockInitDataType } from '../../../query/useBlockInitDataQuery';
import { NetworkCategory } from '../../../value-object/NetworkCategory';
import { isSettingsComplete } from './useSettingsErrorNotificationProps';

describe( '[Happy] useSettingsErrorNotificationProps.isSettingsIncomplete', () => {
	/**
	 * データがundefinedの場合、undefinedを返すこと
	 */
	it( '[E745DD16] should return undefined when data is undefined', () => {
		// ARRANGE
		const data: BlockInitDataType | undefined = undefined;

		// ACT
		const result = isSettingsComplete( data );

		// ASSERT
		expect( result ).toBeUndefined();
	} );

	/**
	 * 選択可能なネットワークカテゴリが存在しない場合、falseを返すこと
	 */
	it( '[9F3EF939] should return false when sellableNetworkCategories is empty', () => {
		// ARRANGE
		const data: BlockInitDataType = {
			sellableNetworkCategories: [],
		};

		// ACT
		const result = isSettingsComplete( data );

		// ASSERT
		expect( result ).toBe( false );
	} );

	/**
	 * 選択可能なネットワークカテゴリが1つ以上存在する場合、trueを返すこと
	 */
	it( '[EDE2FC9B] should return true when sellableNetworkCategories has at least one item', () => {
		// ARRANGE
		const data: BlockInitDataType = {
			sellableNetworkCategories: [
				NetworkCategory.from( NetworkCategoryId.from( 1 ), 'Test Network', [ Symbol.from( 'TEST' ) ] ),
			],
		};

		// ACT
		const result = isSettingsComplete( data );

		// ASSERT
		expect( result ).toBe( true );
	} );

	/**
	 * 選択可能なネットワークカテゴリが複数存在する場合、trueを返すこと
	 */
	it( '[69FAC78D] should return true when sellableNetworkCategories has multiple items', () => {
		// ARRANGE
		const data: BlockInitDataType = {
			sellableNetworkCategories: [
				NetworkCategory.from( NetworkCategoryId.from( 1 ), 'Test Network 1', [ Symbol.from( 'TEST1' ) ] ),
				NetworkCategory.from( NetworkCategoryId.from( 2 ), 'Test Network 2', [ Symbol.from( 'TEST2' ) ] ),
			],
		};

		// ACT
		const result = isSettingsComplete( data );

		// ASSERT
		expect( result ).toBe( true );
	} );
} );
