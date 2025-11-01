import { useMemo } from 'react';
import { ScreenNotifier } from '../../../../lib/gutenberg/notification/ScreenNotifier';
import { useIsInvalidInputAmountDecimals } from '../hooks/useIsInvalidInputAmountDecimals';
import { TextProvider } from '../../../../lib/i18n/TextProvider';
import { InvalidDecimalsNotificationProps } from './InvalidDecimalsNotification';

export const useInvalidDecimalsNotificationProps = (): InvalidDecimalsNotificationProps => {
	return {
		isError: useIsInvalidInputAmountDecimals(),
		screenNotifier: useMemo( () => new ScreenNotifier(), [] ),
		textProvider: useMemo( () => new TextProvider(), [] ),
	};
};
