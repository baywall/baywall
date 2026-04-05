import { useMemo } from '@wordpress/element';
import { ScreenNotifier } from '../../../../lib/gutenberg/notification/ScreenNotifier';
import { AmountErrorNotificationProps } from './AmountErrorNotification';
import { useInputSellingPriceAmount } from '../../hooks/useInputSellingPriceAmount';

export const useAmountErrorNotificationProps = (): AmountErrorNotificationProps => {
	return {
		isError: useIsError(),
		screenNotifier: useMemo( () => new ScreenNotifier(), [] ),
	};
};

const useIsError = (): boolean => {
	const inputAmount = useInputSellingPriceAmount();

	// nullの時だけ不正な値が入力されたとみなす（undefinedはロード中のためエラーとしない）
	return inputAmount === null;
};
