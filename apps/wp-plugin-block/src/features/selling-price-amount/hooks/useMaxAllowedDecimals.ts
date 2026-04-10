import { useMemo } from '@wordpress/element';
import { useTokens } from '../../../hooks/useTokens';
import { getMaxDecimals } from '../lib/getMaxDecimals';
import { NetworkCategoryId, Symbol } from '@serendipity/lib-value-object';

/**
 * 指定可能な小数点以下桁数の最大値を取得します
 * @param networkCategoryId
 * @param symbol
 */
export const useMaxAllowedDecimals = (
	networkCategoryId: NetworkCategoryId | null | undefined,
	symbol: Symbol | null | undefined
) => {
	const tokens = useTokens();

	return useMemo( () => {
		if ( tokens === undefined || networkCategoryId === undefined || symbol === undefined ) {
			return undefined;
		} else if ( networkCategoryId === null || symbol === null ) {
			return null;
		}

		// 同一ネットワークカテゴリのToken一覧から最大小数点以下桁数を取得できる場合はその値を返す
		const networkTokens = tokens.filter( ( t ) => t.networkCategoryId.equals( networkCategoryId ) );
		const networkMaxDecimals = getMaxDecimals( symbol, networkTokens );
		if ( networkMaxDecimals ) {
			return networkMaxDecimals;
		}

		// 見つからない場合は全ネットワークから最大小数点以下桁数を取得して返す
		return getMaxDecimals( symbol, tokens );
	}, [ tokens, networkCategoryId, symbol ] );
};
