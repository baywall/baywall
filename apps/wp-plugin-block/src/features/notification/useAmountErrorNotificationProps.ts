import { useMemo } from '@wordpress/element';
import { ScreenNotifier } from '../../lib/gutenberg/notification/ScreenNotifier';
import { TextProvider } from '../../lib/i18n/TextProvider';
import { AmountErrorNotificationProps } from './AmountErrorNotification';
import { useInputSellingPriceAmountState } from '../selling-price-amount/hooks/useInputSellingPriceAmountState';

export const useAmountErrorNotificationProps = (): AmountErrorNotificationProps => {
	const [ sellingPriceAmount ] = useInputSellingPriceAmountState();

	const isError = sellingPriceAmount === null; // 販売価格の値がnullの場合、不正な文字列が入力されたものとみなす
	const screenNotifier = useMemo( () => new ScreenNotifier(), [] );
	const textProvider = useMemo( () => new TextProvider(), [] );

	return {
		isError,
		screenNotifier,
		textProvider,
	};
};
