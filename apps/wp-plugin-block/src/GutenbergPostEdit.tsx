import { Placeholder } from '@wordpress/components';
import { widget } from '@wordpress/icons';
import { useInitWidgetState } from './features/initialize/useInitWidgetState';
import { useUpdateWidgetAttributes } from './features/update/useUpdateWidgetAttributes';
import { SellingSymbolSelect } from './features/selling-symbol/SellingSymbolSelect';
import { useSellingSymbolSelectProps } from './features/selling-symbol/useSellingSymbolSelectProps';
import { SellingNetworkCategorySelect } from './features/selling-network-category/SellingNetworkCategorySelect';
import { useSellingNetworkCategorySelectProps } from './features/selling-network-category/useSellingNetworkCategorySelectProps';
import { SellingPriceAmount } from './features/selling-price-amount/SellingPriceAmount';
import { useSellingPriceAmountProps } from './features/selling-price-amount/useSellingPriceAmountProps';

type GutenbergPostEditProps = {};

export const GutenbergPostEdit: React.FC< GutenbergPostEditProps > = ( {} ) => {
	// ウィジェットの状態を初期化
	useInitWidgetState();
	// ウィジェットの属性を更新
	useUpdateWidgetAttributes();

	return (
		<Placeholder icon={ widget } label={ 'Qik Chain Pay' }>
			<div style={ { width: '100%' } }>
				<SellingNetworkCategorySelect { ...useSellingNetworkCategorySelectProps() } />
			</div>
			<div style={ { display: 'flex', alignItems: 'flex-end' } }>
				<SellingPriceAmount { ...useSellingPriceAmountProps() } style={ { width: '150px' } } />
				<SellingSymbolSelect { ...useSellingSymbolSelectProps() } />
			</div>
		</Placeholder>
	);
};
