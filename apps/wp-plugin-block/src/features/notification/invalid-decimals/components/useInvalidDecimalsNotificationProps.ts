import { useMemo } from 'react';
import { ScreenNotifier } from '../../../../lib/gutenberg/notification/ScreenNotifier';
import { useIsInvalidInputAmountDecimals } from '../hooks/useIsInvalidInputAmountDecimals';
import { InvalidDecimalsNotificationProps } from './InvalidDecimalsNotification';

export const useInvalidDecimalsNotificationProps = (): InvalidDecimalsNotificationProps => {
	return {
		isError: useIsInvalidInputAmountDecimals(),
		screenNotifier: useMemo( () => new ScreenNotifier(), [] ),
	};
};
