import { useMemo } from '@wordpress/element';
import { ScreenNotifier } from '../../lib/gutenberg/notification/ScreenNotifier';
import { TextProvider } from '../../lib/i18n/TextProvider';
import { useSellingPriceAmount } from '../../provider/selling-price-amount/useSellingPriceAmount';
import { AmountErrorNotificationProps } from './AmountErrorNotification';

export const useAmountErrorNotificationProps = (): AmountErrorNotificationProps => {
	const { sellingPriceAmount } = useSellingPriceAmount();

	const isError = sellingPriceAmount === null; // 販売価格の値がnullの場合、不正な文字列が入力されたものとみなす
	const screenNotifier = useMemo( () => new ScreenNotifier(), [] );
	const textProvider = useMemo( () => new TextProvider(), [] );

	return {
		isError,
		screenNotifier,
		textProvider,
	};
};
