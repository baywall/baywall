import { useMemo } from 'react';
import cc from 'currency-codes';
import { Decimals } from '@serendipity/lib-value-object';
import { usePostSettingQuery } from '../../../../../types/gql/generated';
import { useSelectedNetworkCategoryIdState } from '../../../selling-network-category/hooks/useSelectedNetworkCategoryIdState';
import { useSelectedSellingPriceSymbolState } from '../../../selling-price-symbol/hooks/useSelectedSellingPriceSymbolState';

/** 画面で選択されている通貨シンボルの最大小数点以下桁数を取得します */
export const useSelectedSymbolsMaxDecimals = () => {
	const { data } = usePostSettingQuery();
	const [ selectedNetworkCategoryId ] = useSelectedNetworkCategoryIdState();
	const [ selectedSellingPriceSymbol ] = useSelectedSellingPriceSymbolState();

	return useMemo( () => {
		if (
			data === undefined ||
			selectedNetworkCategoryId === undefined ||
			selectedSellingPriceSymbol === undefined
		) {
			return undefined;
		} else if ( selectedNetworkCategoryId === null || selectedSellingPriceSymbol === null ) {
			return null;
		}

		const codeRecord = cc.code( selectedSellingPriceSymbol.value );
		if ( codeRecord ) {
			// 法定通貨の場合はcurrency-codesの情報を使用
			return Decimals.from( codeRecord.digits );
		}

		// 同一ネットワークカテゴリにある同一トークン名称の小数点以下桁数を取得
		const networkDecimalsValues: number[] = data.tokens
			.filter(
				( t ) =>
					t.chain.networkCategory.id === selectedNetworkCategoryId.value &&
					t.symbol === selectedSellingPriceSymbol.value
			)
			.map( ( t ) => t.decimals );

		// 同一ネットワークに同一シンボルのトークンがある場合はその中で最大の桁数を返す
		if ( networkDecimalsValues.length > 0 ) {
			return Decimals.from( Math.max( ...networkDecimalsValues ) );
		}

		// 全ネットワークカテゴリにある同一トークン名称の小数点以下桁数を取得
		const allNetworkDecimalsValues: number[] = data.tokens
			.filter( ( t ) => t.symbol === selectedSellingPriceSymbol.value )
			.map( ( t ) => t.decimals );

		// 見つかった一覧のうち最大の桁数を返す
		return allNetworkDecimalsValues.length > 0 ? Decimals.from( Math.max( ...allNetworkDecimalsValues ) ) : null;
	}, [ data, selectedNetworkCategoryId, selectedSellingPriceSymbol ] );
};
