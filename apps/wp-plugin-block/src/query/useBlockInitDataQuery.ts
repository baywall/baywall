import { NetworkCategoryId, Symbol } from '@serendipity/lib-value-object';
import { usePostSettingQuery } from '../../types/gql/generated';
import { SellableCurrency } from '../value-object/SellableCurrency';
import { NetworkCategory } from '../value-object/NetworkCategory';

export const useBlockInitDataQuery = () => {
	return usePostSettingQuery( undefined, {
		select: ( data ) => {
			// 販売可能なネットワークカテゴリ一覧
			const sellableNetworkCategories = data.networkCategories.map( ( category ) =>
				NetworkCategory.from( NetworkCategoryId.from( category.id ), category.name )
			);

			// 販売可能な通貨一覧
			const sellableCurrencies = data.networkCategories.flatMap( ( category ) =>
				category.sellableSymbols.map( ( symbol ) =>
					SellableCurrency.from( NetworkCategoryId.from( category.id ), Symbol.from( symbol ) )
				)
			);

			return { sellableNetworkCategories, sellableCurrencies };
		},
	} );
};
