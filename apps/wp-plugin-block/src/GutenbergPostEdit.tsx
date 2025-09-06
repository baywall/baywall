import { Placeholder } from '@wordpress/components';
import { SellingPriceSymbolSelect } from './features/selling-price-symbol/SellingPriceSymbolSelect';
import { useSellingPriceSymbolSelectProps } from './features/selling-price-symbol/useSellingSymbolSelectProps';
import { SellingNetworkCategorySelect } from './features/selling-network-category/SellingNetworkCategorySelect';
import { useSellingNetworkCategorySelectProps } from './features/selling-network-category/useSellingNetworkCategorySelectProps';
import { SellingPriceAmount } from './features/selling-price-amount/SellingPriceAmount';
import { useSellingPriceAmountProps } from './features/selling-price-amount/useSellingPriceAmountProps';
import { useSyncWidgetAttributes } from './features/widget-attributes/useSyncWidgetAttributes';
import { AmountErrorNotification } from './features/notification/AmountErrorNotification';
import { useAmountErrorNotificationProps } from './features/notification/useAmountErrorNotificationProps';
import { SettingsErrorNotification } from './features/notification/SettingsErrorNotification';
import { ApiErrorNotification } from './features/notification/ApiErrorNotification';
import { useInitialize } from './features/initialize/useInitialize';
import { useControlEditorSaving } from './features/control-editor-saving/useControlEditorSaving';
import { BlockIconProvider } from './lib/icon/BlockIconProvider';

type GutenbergPostEditProps = {};

export const GutenbergPostEdit: React.FC< GutenbergPostEditProps > = ( {} ) => {
	useInitialize(); // 初期化処理
	useSyncWidgetAttributes(); // Attributesと画面の状態を同期
	useControlEditorSaving(); // 投稿の保存制御

	return (
		<Placeholder
			icon={ new BlockIconProvider().get() }
			label={ 'Qik Chain Pay' }
			id="fd9e15e3-9f4f-4537-8470-3da48e66d6e9"
		>
			<div style={ { width: '100%', display: 'flex', flexDirection: 'column', gap: '2em' } }>
				<div style={ { width: '100%' } }>
					<ApiErrorNotification />
					<SettingsErrorNotification />
				</div>

				<div style={ { display: 'flex', alignItems: 'center', gap: '1.5em' } }>
					<SellingNetworkCategorySelect { ...useSellingNetworkCategorySelectProps() } />
				</div>

				<div style={ { display: 'flex', alignItems: 'center', gap: '0.75em' } }>
					<SellingPriceAmount
						{ ...useSellingPriceAmountProps() }
						style={ { width: '150px', maxHeight: '32px', minHeight: '32px' } }
					/>
					<SellingPriceSymbolSelect { ...useSellingPriceSymbolSelectProps() } />

					{ /* 販売価格の値が不正な時に通知を行うコンポーネント */ }
					<AmountErrorNotification { ...useAmountErrorNotificationProps() } />
				</div>
			</div>
		</Placeholder>
	);
};
