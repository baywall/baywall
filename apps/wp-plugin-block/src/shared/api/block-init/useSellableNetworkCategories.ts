import { useMemo } from 'react';
import { useBlockInitRawDataQuery } from './useBlockInitRawDataQuery';
import { isSellableNetworkCategory } from './isSellableNetworkCategory';
import { NetworkCategory } from '../../domain/network-category/NetworkCategory';
import { NetworkCategoryId } from '@baywall/lib-value-object';

/**
 * 販売可能なネットワークカテゴリ一覧を取得します
 */
export const useSellableNetworkCategories = (): NetworkCategory[] | undefined => {
	const { data } = useBlockInitRawDataQuery();

	return useMemo(() => {
		if (data === undefined) {
			return undefined;
		}

		return data.networkCategories
			.filter(isSellableNetworkCategory)
			.map((c) => NetworkCategory.from(NetworkCategoryId.from(c.id), c.name));
	}, [data]);
};
