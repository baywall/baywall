import assert from 'assert';
import { useCallback } from 'react';
import { usePostSetting } from '../../provider/serverData/postSetting/usePostSetting';
import { NetworkCategoryId } from '@serendipity/lib-value-object';

/**
 * 指定されたネットワークで販売可能な通貨シンボル一覧を取得するコールバックを返します。
 */
export const useGetSellableSymbolsCallback = () => {
	const postSetting = usePostSetting(); // サーバーから設定を取得

	return useCallback(
		( networkCategoryId: NetworkCategoryId ) => {
			if ( postSetting === undefined ) {
				return undefined; // 読み込み中
			}

			const selectableSymbols = postSetting.networkCategories.find( ( n ) => n.id === networkCategoryId.value )
				?.sellableSymbols;

			// APIの仕様上、selectableSymbolsはundefinedにはならない
			assert(
				selectableSymbols !== undefined,
				`[519DA805] Sellable symbols is undefined. - networkCategory: ${ networkCategoryId.value }`
			);

			return selectableSymbols;
		},
		[ postSetting ]
	);
};
