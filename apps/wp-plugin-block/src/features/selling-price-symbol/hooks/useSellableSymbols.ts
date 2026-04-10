import { useMemo } from '@wordpress/element';
import { NetworkCategoryId, Symbol } from '@serendipity/lib-value-object';
import { useLogger } from '@serendipity/lib-frontend';
import { useBlockInitRawDataQuery } from '../../../query/useBlockInitRawDataQuery';

/**
 * 販売可能な通貨シンボル一覧を取得します
 * @param selectedNetworkCategoryId
 */
export const useSellableSymbols = (
	selectedNetworkCategoryId: NetworkCategoryId | null | undefined
): Symbol[] | null | undefined => {
	const logger = useLogger();
	const { data } = useBlockInitRawDataQuery();

	return useMemo( () => {
		if ( selectedNetworkCategoryId === undefined || data === undefined ) {
			return undefined;
		} else if ( selectedNetworkCategoryId === null ) {
			return [];
		}

		const sellableSymbols = data.networkCategories
			.find( ( n ) => NetworkCategoryId.from( n.id ).equals( selectedNetworkCategoryId ) )
			?.sellableSymbols.map( ( s ) => Symbol.from( s ) );

		if ( sellableSymbols === undefined ) {
			// 期待しない状態なのでログを出力しておく
			logger.warn(
				`[89175506] selectedNetworkCategoryId: ${ selectedNetworkCategoryId }, sellableSymbols: ${ sellableSymbols }`
			);
			return null;
		}

		return sellableSymbols;
	}, [ logger, selectedNetworkCategoryId, data ] );
};
