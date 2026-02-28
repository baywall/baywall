import { useMemo } from 'react';
import { ScreenNotifier } from '../../../lib/gutenberg/notification/ScreenNotifier';
import { InvalidDecimalsNotificationProps } from './InvalidDecimalsNotification';
import { useInputSellingPriceAmountState } from '../../selling-price-amount/hooks/useInputSellingPriceAmountState';
import { useIsDecimalPlacesError } from './hooks/useIsDecimalPlacesError';
import { useBlockInitDataQuery } from '../../../query/useBlockInitDataQuery';
import { useSelectedNetworkCategoryIdState } from '../../selling-network-category/hooks/useSelectedNetworkCategoryIdState';
import { useSelectedSellingPriceSymbolState } from '../../selling-price-symbol/hooks/useSelectedSellingPriceSymbolState';
import { getMaxDecimals } from './lib/getMaxDecimals';

export const useInvalidDecimalsNotificationProps = (): InvalidDecimalsNotificationProps => {
	return {
		isError: useIsError(),
		screenNotifier: useMemo( () => new ScreenNotifier(), [] ),
	};
};

const useIsError = (): boolean => {
	const [ inputAmount ] = useInputSellingPriceAmountState();
	const { data } = useBlockInitDataQuery();
	const [ selectedNetworkCategoryId ] = useSelectedNetworkCategoryIdState();
	const [ selectedSellingPriceSymbol ] = useSelectedSellingPriceSymbolState();

	const maxDecimals = useMemo( () => {
		if (
			data === undefined ||
			selectedNetworkCategoryId === undefined ||
			selectedSellingPriceSymbol === undefined
		) {
			return undefined;
		} else if ( selectedNetworkCategoryId === null || selectedSellingPriceSymbol === null ) {
			return null;
		}

		// 同一ネットワークカテゴリのToken一覧から最大小数点以下桁数を取得できる場合はその値を返す
		const networkTokens = data.tokens.filter( ( t ) => t.networkCategoryId.equals( selectedNetworkCategoryId ) );
		const networkMaxDecimals = getMaxDecimals( selectedSellingPriceSymbol, networkTokens );
		if ( networkMaxDecimals ) {
			return networkMaxDecimals;
		}

		// 見つからない場合は全ネットワークから最大小数点以下桁数を取得して返す
		return getMaxDecimals( selectedSellingPriceSymbol, data.tokens );
	}, [ data, selectedNetworkCategoryId, selectedSellingPriceSymbol ] );

	return useIsDecimalPlacesError( inputAmount, maxDecimals );
};
