import { useCallback, useEffect } from 'react';
import { NetworkCategoryId, Symbol } from '@serendipity/lib-value-object';
import { PostSettingQuery, usePostSettingQuery } from '../../types/gql/generated';
import { NetworkCategory } from '../value-object/NetworkCategory';
import { useLogger } from '@serendipity/lib-frontend';

/** ブロック初期データ取得クエリ */
export const useBlockInitDataQuery = () => {
	const logger = useLogger();
	const select = useSelectCallback();
	const result = usePostSettingQuery( undefined, {
		select,
	} );

	const { error } = result;
	useEffect( () => {
		if ( error ) {
			logger.error( '[0A5DA9DA]', error );
		}
	}, [ logger, error ] );

	return result;
};

const useSelectCallback = () => {
	return useCallback( ( data: PostSettingQuery ) => {
		// 販売可能なネットワークカテゴリ一覧
		const sellableNetworkCategories: NetworkCategory[] = data.networkCategories
			.sort( ( a, b ) => a.id - b.id )
			.map( ( category ) => NetworkCategory.from( NetworkCategoryId.from( category.id ), category.name ) );

		// 販売可能なシンボル一覧
		const sellableSymbols: Symbol[] = data.sellableSymbols.sort().map( ( symbol ) => Symbol.from( symbol ) );

		return { sellableNetworkCategories, sellableSymbols };
	}, [] );
};
