import { useMemo } from '@wordpress/element';
import { ScreenNotifier } from '../../../lib/gutenberg/notification/ScreenNotifier';
import { AmountErrorNotificationProps } from './AmountErrorNotification';
import { useInputSellingPriceAmountState } from '../../selling-price-amount/hooks/useInputSellingPriceAmountState';
import { isInvalidInputAmount } from './lib/isInvalidInputAmount';

export const useAmountErrorNotificationProps = (): AmountErrorNotificationProps => {
	return {
		isError: useIsError(),
		screenNotifier: useMemo( () => new ScreenNotifier(), [] ),
	};
};

const useIsError = (): boolean => {
	return isInvalidInputAmount( useInputSellingPriceAmountState()[ 0 ] );
};
