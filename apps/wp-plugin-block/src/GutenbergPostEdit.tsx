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
import { BlockEditProps } from '@wordpress/blocks';
import { WidgetAttributes } from './types/WidgetAttributes';
import { useSyncWidgetAttributes } from './features/widget-attributes/useSyncWidgetAttributes';

type GutenbergPostEditProps = {
	blockEditProps: BlockEditProps< WidgetAttributes >;
};

export const GutenbergPostEdit: React.FC< GutenbergPostEditProps > = ( { blockEditProps } ) => {
	useSyncWidgetAttributes( blockEditProps ); // 画面で設定された値と保存時の属性を同期

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
