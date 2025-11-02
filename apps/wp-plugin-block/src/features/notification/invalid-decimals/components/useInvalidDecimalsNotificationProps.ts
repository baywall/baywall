import { useMemo } from 'react';
import { ScreenNotifier } from '../../../../lib/gutenberg/notification/ScreenNotifier';
import { InvalidDecimalsNotificationProps } from './InvalidDecimalsNotification';
import { useInputSellingPriceAmountState } from '../../../selling-price-amount/hooks/useInputSellingPriceAmountState';
import { useSelectedSymbolsMaxDecimals } from '../hooks/useSelectedSymbolsMaxDecimals';
import { useIsDecimalPlacesError } from '../hooks/useIsDecimalPlacesError';

export const useInvalidDecimalsNotificationProps = (): InvalidDecimalsNotificationProps => {
	return {
		isError: useIsError(),
		screenNotifier: useMemo( () => new ScreenNotifier(), [] ),
	};
};

const useIsError = (): boolean => {
	const [ inputAmount ] = useInputSellingPriceAmountState();
	const maxDecimals = useSelectedSymbolsMaxDecimals();

	return useIsDecimalPlacesError( inputAmount, maxDecimals );
};
