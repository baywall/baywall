import { Placeholder } from '@wordpress/components';
import { SellingPriceSymbolSelect } from './features/selling-price-symbol/components/SellingPriceSymbolSelect';
import { useSellingPriceSymbolSelectProps } from './features/selling-price-symbol/components/useSellingSymbolSelectProps';
import { SellingNetworkCategorySelect } from './features/selling-network-category/components/SellingNetworkCategorySelect';
import { useSellingNetworkCategorySelectProps } from './features/selling-network-category/components/useSellingNetworkCategorySelectProps';
import { SellingPriceAmount } from './features/selling-price-amount/components/SellingPriceAmount';
import { useSellingPriceAmountProps } from './features/selling-price-amount/components/useSellingPriceAmountProps';
import { useSyncWidgetAttributes } from './features/widget-attributes/useSyncWidgetAttributes';
import { AmountErrorNotification } from './features/notification/amount-error/AmountErrorNotification';
import { useAmountErrorNotificationProps } from './features/notification/amount-error/useAmountErrorNotificationProps';
import { SettingsErrorNotification } from './features/notification/settings-error/SettingsErrorNotification';
import { ApiErrorNotification } from './features/notification/api-error/ApiErrorNotification';
import { useControlEditorSaving } from './features/control-editor-saving/useControlEditorSaving';
import { BlockIconProvider } from './lib/icon/BlockIconProvider';
import { InvalidDecimalsNotification } from './features/notification/invalid-decimals/InvalidDecimalsNotification';
import { useInvalidDecimalsNotificationProps } from './features/notification/invalid-decimals/useInvalidDecimalsNotificationProps';
import { useTranslation } from 'react-i18next';
import { useSettingsErrorNotificationProps } from './features/notification/settings-error/useSettingsErrorNotificationProps';
import { useApiErrorNotificationProps } from './features/notification/api-error/useApiErrorNotificationProps';

type GutenbergPostEditProps = {};

export const GutenbergPostEdit: React.FC< GutenbergPostEditProps > = ( {} ) => {
	const { t } = useTranslation();
	useSyncWidgetAttributes(); // Attributesと画面の状態を同期
	useControlEditorSaving(); // 投稿の保存制御

	return (
		<Placeholder
			icon={ new BlockIconProvider().get() }
			label={ 'baywall' }
			instructions={ t( 'select_network_category_and_price_message' ) }
			id="fd9e15e3-9f4f-4537-8470-3da48e66d6e9"
		>
			{ /* エラー表示 */ }
			<div style={ { width: '100%' } }>
				<ApiErrorNotification { ...useApiErrorNotificationProps() } />
				<SettingsErrorNotification { ...useSettingsErrorNotificationProps() } />
			</div>

			{ /* 設定項目 ※管理画面のcss(.from-table)を流用 */ }
			<table className="form-table">
				<tbody>
					{ /* ネットワークカテゴリ設定 */ }
					<tr>
						<th scope="row">{ t( 'network_category_label' ) }:</th>
						<td>
							<div style={ { display: 'flex', alignItems: 'center', gap: '0.75em' } }>
								<SellingNetworkCategorySelect { ...useSellingNetworkCategorySelectProps() } />
							</div>
						</td>
					</tr>

					{ /* 販売価格設定 */ }
					<tr>
						<th scope="row">{ t( 'price_label' ) }:</th>
						<td>
							<div style={ { display: 'flex', alignItems: 'center', gap: '0.75em' } }>
								<SellingPriceAmount
									{ ...useSellingPriceAmountProps() }
									style={ { width: '135px', maxHeight: '32px', minHeight: '32px' } }
								/>
								<SellingPriceSymbolSelect { ...useSellingPriceSymbolSelectProps() } />

								{ /* 販売価格の値が不正な時に通知を行うコンポーネント */ }
								<AmountErrorNotification { ...useAmountErrorNotificationProps() } />
								{ /* 販売価格の小数点以下桁数が不正な時に通知を行うコンポーネント */ }
								<InvalidDecimalsNotification { ...useInvalidDecimalsNotificationProps() } />
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</Placeholder>
	);
};
