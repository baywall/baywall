import { type BlockInitRawDataType } from '../../../../query/useBlockInitRawDataQuery';

/**
 * 設定が完了しているかどうかを判定します
 * @param data
 */
export const isSettingsComplete = ( data: BlockInitRawDataType | undefined ): boolean | undefined => {
	if ( data === undefined ) {
		return undefined;
	}

	if ( ! existsSelectableNetworkCategory( data ) ) {
		// 選択可能なネットワークカテゴリが存在しない場合はサーバー設定が未完了
		return false;
	}

	return true;
};

/**
 * 選択可能なネットワークカテゴリが存在するかどうかを判定します
 * @param data
 */
const existsSelectableNetworkCategory = ( data: BlockInitRawDataType ): boolean => {
	return data.networkCategories.some( ( category ) => category.sellableSymbols.length > 0 );
};
