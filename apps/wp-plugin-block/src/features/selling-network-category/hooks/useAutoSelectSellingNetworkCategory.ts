import { useEffect } from 'react';
import { useSavedSellingNetworkCategoryId } from '../../widget-attributes/useSavedSellingNetworkCategoryId';
import { useSelectedNetworkCategoryIdState } from './useSelectedNetworkCategoryIdState';
import { useSellableNetworkCategories } from './useSellableNetworkCategories';

/** 販売ネットワークカテゴリを自動的に選択します */
export const useAutoSelectSellingNetworkCategory = (): void => {
	const sellableNetworkCategories = useSellableNetworkCategories();
	const [ selectedNetworkCategoryId, setSelectedNetworkCategoryId ] = useSelectedNetworkCategoryIdState();
	const savedNetworkCategoryId = useSavedSellingNetworkCategoryId();

	useEffect( () => {
		if ( selectedNetworkCategoryId !== undefined ) {
			return; // すでに選択されている場合は何もしない
		}
		if ( savedNetworkCategoryId ) {
			// 初期化処理: 保存したネットワークカテゴリIDが存在する場合はロード
			setSelectedNetworkCategoryId( savedNetworkCategoryId );
		} else if ( sellableNetworkCategories ) {
			// 初期化処理: 販売可能なネットワークカテゴリが存在する場合は先頭のカテゴリを選択
			setSelectedNetworkCategoryId( sellableNetworkCategories[ 0 ]?.id ?? null );
		}
	}, [ sellableNetworkCategories, savedNetworkCategoryId, selectedNetworkCategoryId, setSelectedNetworkCategoryId ] );
};
