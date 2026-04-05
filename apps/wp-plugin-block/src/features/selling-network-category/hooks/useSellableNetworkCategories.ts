import { useMemo } from 'react';
import { useBlockInitRawDataQuery } from '../../../query/useBlockInitRawDataQuery';
import { NetworkCategory } from '../../../value-object/NetworkCategory';
import { NetworkCategoryId } from '@serendipity/lib-value-object';

/**
 * 販売可能なネットワークカテゴリ一覧を取得します
 */
export const useSellableNetworkCategories = (): NetworkCategory[] | undefined => {
	const { data } = useBlockInitRawDataQuery();

	return useMemo( () => {
		if ( data === undefined ) {
			return undefined;
		}

		return data.networkCategories
			.filter( ( c ) => c.sellableSymbols.length > 0 )
			.map( ( c ) => NetworkCategory.from( NetworkCategoryId.from( c.id ), c.name ) );
	}, [ data ] );
};
