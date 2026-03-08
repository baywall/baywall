import { useCallback, useEffect } from 'react';
import { Decimals, NetworkCategoryId, Symbol } from '@serendipity/lib-value-object';
import { PostSettingQuery, usePostSettingQuery } from '../types/gql/generated';
import { NetworkCategory } from '../value-object/NetworkCategory';
import { useLogger } from '@serendipity/lib-frontend';
import { Token } from '../value-object/Token';

/** ブロック初期データの型 */
export type BlockInitDataType = NonNullable< ReturnType< typeof useBlockInitDataQuery >[ 'data' ] >;

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
			.filter( ( category ) => category.sellableSymbols.length > 0 ) // 販売可能なシンボルが存在するカテゴリのみ対象
			.sort( ( a, b ) => a.id - b.id )
			.map( ( category ) =>
				NetworkCategory.from(
					NetworkCategoryId.from( category.id ),
					category.name,
					category.sellableSymbols.sort().map( ( symbol ) => Symbol.from( symbol ) )
				)
			);

		const tokens: Token[] = data.tokens.map( ( t ) => {
			return Token.from(
				NetworkCategoryId.from( t.chain.networkCategory.id ),
				Symbol.from( t.symbol ),
				Decimals.from( t.decimals )
			);
		} );

		return {
			/** 販売可能なネットワークカテゴリ一覧 */
			sellableNetworkCategories,
			/** サーバーに登録済みのトークン一覧 */
			tokens,
		};
	}, [] );
};
