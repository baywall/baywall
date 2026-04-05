import { useCallback } from 'react';
import { NetworkCategoryId } from '@serendipity/lib-value-object';
import { type SellingNetworkCategorySelectProps } from './SellingNetworkCategorySelect';
import { useSelectedNetworkCategoryIdState } from '../hooks/useSelectedNetworkCategoryIdState';

/** 販売ネットワークカテゴリの選択肢が変更されたときのコールバックを取得します */
export const useSellingNetworkCategorySelectOnChange = (): SellingNetworkCategorySelectProps[ 'onChange' ] => {
	const [ , setSelectedNetworkCategoryId ] = useSelectedNetworkCategoryIdState();

	return useCallback< NonNullable< SellingNetworkCategorySelectProps[ 'onChange' ] > >(
		( value ) => {
			setSelectedNetworkCategoryId( NetworkCategoryId.from( Number( value ) ) );
		},
		[ setSelectedNetworkCategoryId ]
	);
};
