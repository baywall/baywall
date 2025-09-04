import { Placeholder } from '@wordpress/components';
import { widget } from '@wordpress/icons';
import { SellingPriceSymbolSelect } from './features/selling-price-symbol/SellingPriceSymbolSelect';
import { useSellingPriceSymbolSelectProps } from './features/selling-price-symbol/useSellingSymbolSelectProps';
import { SellingNetworkCategorySelect } from './features/selling-network-category/SellingNetworkCategorySelect';
import { useSellingNetworkCategorySelectProps } from './features/selling-network-category/useSellingNetworkCategorySelectProps';
import { SellingPriceAmount } from './features/selling-price-amount/SellingPriceAmount';
import { useSellingPriceAmountProps } from './features/selling-price-amount/useSellingPriceAmountProps';
import { useSyncWidgetAttributes } from './features/widget-attributes/useSyncWidgetAttributes';
import { AmountErrorNotification } from './features/notification/AmountErrorNotification';
import { useAmountErrorNotificationProps } from './features/notification/useAmountErrorNotificationProps';

type GutenbergPostEditProps = {};

export const GutenbergPostEdit: React.FC< GutenbergPostEditProps > = ( {} ) => {
	useSyncWidgetAttributes(); // Attributesと画面の状態を同期

	return (
		<Placeholder icon={ widget } label={ 'Qik Chain Pay' }>
			<div style={ { width: '100%' } }>
				<SellingNetworkCategorySelect { ...useSellingNetworkCategorySelectProps() } />
			</div>
			<div style={ { display: 'flex', alignItems: 'flex-end' } }>
				<SellingPriceAmount { ...useSellingPriceAmountProps() } style={ { width: '150px' } } />
				<SellingPriceSymbolSelect { ...useSellingPriceSymbolSelectProps() } />

				{ /* 販売価格の値が不正な時に通知を行うコンポーネント */ }
				<AmountErrorNotification { ...useAmountErrorNotificationProps() } />
			</div>
		</Placeholder>
	);
};
