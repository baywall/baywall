import { useMemo } from 'react';
import { ScreenNotifier } from '../../../../lib/gutenberg/notification/ScreenNotifier';
import { InvalidDecimalsNotificationProps } from './InvalidDecimalsNotification';
import { useIsDecimalPlacesError } from '../../hooks/useIsDecimalPlacesError';
import { useSelectedNetworkCategoryIdState } from '../../../selling-network-category/hooks/useSelectedNetworkCategoryIdState';
import { useSelectedSellingPriceSymbolState } from '../../../selling-price-symbol/hooks/useSelectedSellingPriceSymbolState';
import { useInputSellingPriceAmount } from '../../hooks/useInputSellingPriceAmount';
import { useMaxAllowedDecimals } from '../../hooks/useMaxAllowedDecimals';

export const useInvalidDecimalsNotificationProps = (): InvalidDecimalsNotificationProps => {
	return {
		isError: useIsError(),
		screenNotifier: useMemo( () => new ScreenNotifier(), [] ),
	};
};

const useIsError = (): boolean => {
	const inputAmount = useInputSellingPriceAmount();
	const [ selectedNetworkCategoryId ] = useSelectedNetworkCategoryIdState();
	const [ selectedSellingPriceSymbol ] = useSelectedSellingPriceSymbolState();

	const maxDecimals = useMaxAllowedDecimals( selectedNetworkCategoryId, selectedSellingPriceSymbol );

	return useIsDecimalPlacesError( inputAmount, maxDecimals );
};
