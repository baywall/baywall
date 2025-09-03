import { useMemo } from '@wordpress/element';
import { NetworkCategoryId, Symbol } from '@serendipity/lib-value-object';
import { usePostSettingQuery } from '../../types/gql/generated';
import { SellableCurrency } from '../value-object/SellableCurrency';

export const useBlockInitDataQuery = () => {
	const { data, ...rest } = usePostSettingQuery();

	const newData = useMemo( () => {
		if ( data === undefined ) {
			return undefined;
		}

		// 販売可能なネットワークカテゴリID一覧
		const sellableNetworkCategoryIds = data.networkCategories.map( ( category ) =>
			NetworkCategoryId.from( category.id )
		);

		// 販売可能な通貨一覧
		const sellableCurrencies = data.networkCategories.flatMap( ( category ) =>
			category.sellableSymbols.map( ( symbol ) =>
				SellableCurrency.from( NetworkCategoryId.from( category.id ), Symbol.from( symbol ) )
			)
		);

		return { sellableNetworkCategoryIds, sellableCurrencies };
	}, [ data ] );

	return { data: newData, ...rest };
};
