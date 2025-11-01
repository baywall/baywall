import { useMemo } from 'react';
import { Symbol } from '@serendipity/lib-value-object';
import { useBlockInitDataQuery } from '../../../query/useBlockInitDataQuery';
import { useSelectedNetworkCategoryIdState } from '../../selling-network-category/hooks/useSelectedNetworkCategoryIdState';
import { useLogger } from '@serendipity/lib-frontend';

/** 画面に表示する販売可能な通貨シンボル一覧を取得します */
export const useSellableSymbols = (): Symbol[] | null | undefined => {
	const logger = useLogger();
	const [ selectedNetworkCategoryId ] = useSelectedNetworkCategoryIdState();
	const { data } = useBlockInitDataQuery();

	return useMemo( () => {
		if ( selectedNetworkCategoryId === undefined || data === undefined ) {
			return undefined;
		} else if ( selectedNetworkCategoryId === null ) {
			return null;
		}

		const sellableSymbols = data.sellableNetworkCategories.find( ( n ) => n.id.equals( selectedNetworkCategoryId ) )
			?.sellableSymbols;

		if ( sellableSymbols === undefined || sellableSymbols.length === 0 ) {
			// 期待しない状態なのでログを出力しておく
			logger.warn(
				`[89175506] selectedNetworkCategoryId: ${ selectedNetworkCategoryId }, sellableSymbols: ${ sellableSymbols }`
			);
		}

		// 空配列が返される条件
		// - 現時点では選択できないネットワークカテゴリが過去に登録され、現在はそれをロードした
		return sellableSymbols ?? [];
	}, [ logger, selectedNetworkCategoryId, data ] );
};
