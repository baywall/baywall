import { useMemo } from 'react';
import { ScreenNotifier } from '../../../../lib/gutenberg/notification/ScreenNotifier';
import { InvalidDecimalsNotificationProps } from './InvalidDecimalsNotification';
import { useInputSellingPriceAmountState } from '../../../selling-price-amount/hooks/useInputSellingPriceAmountState';
import { useSelectedSymbolsMaxDecimals } from '../hooks/useSelectedSymbolsMaxDecimals';
import { isValidDecimalPlaces } from '../lib/isValidDecimalPlaces';

export const useInvalidDecimalsNotificationProps = (): InvalidDecimalsNotificationProps => {
	return {
		isError: useIsError(),
		screenNotifier: useMemo( () => new ScreenNotifier(), [] ),
	};
};

const useIsError = (): boolean => {
	const [ inputAmount ] = useInputSellingPriceAmountState();
	const maxDecimals = useSelectedSymbolsMaxDecimals();

	return useMemo( () => {
		if ( ! inputAmount || ! maxDecimals ) {
			return false; // 値が取得できない場合はエラー無しとみなす
		}
		// 有効な小数点以下桁数に収まっていない場合はエラーの判定
		return ! isValidDecimalPlaces( inputAmount, maxDecimals );
	}, [ inputAmount, maxDecimals ] );
};
