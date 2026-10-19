import type { Placeholder as PlaceholderType } from '@wordpress/components';
const Placeholder = window.wp.components.Placeholder as typeof PlaceholderType;
import {
	SellingPriceSymbolSelect,
	useAutoSelectSellingPriceSymbol,
	useSellingPriceSymbolSelectProps,
} from '../features/selling-price-symbol';
import {
	SellingNetworkCategorySelect,
	useAutoSelectSellingNetworkCategory,
	useSellingNetworkCategorySelectProps,
} from '../features/selling-network-category';
import {
	SellingPriceAmount,
	useInitSellingPriceAmountValue,
	useSellingPriceAmountProps,
	useWarnAmountErrorSnackbar,
	useWarnDecimalsErrorSnackbar,
} from '../features/selling-price-amount';
import { useSyncWidgetAttributes } from './useSyncWidgetAttributes';
import { useControlEditorSaving } from '../features/control-editor-saving';
import {
	ApiErrorNotification,
	SettingsErrorNotification,
	useApiErrorNotificationProps,
	useSettingsErrorNotificationProps,
} from '../features/notification';
import { getBlockIcon } from '../shared/lib/icon';
import { useTranslation } from 'react-i18next';

export const GutenbergPostEdit = () => {
	const { t } = useTranslation();
	useSyncWidgetAttributes(); // Attributesと画面の状態を同期
	useControlEditorSaving(); // 投稿の保存制御
	useAutoSelectSellingNetworkCategory(); // 販売ネットワークカテゴリの自動選択
	useInitSellingPriceAmountValue(); // 販売価格の初期値設定
	useAutoSelectSellingPriceSymbol(); // 販売価格の通貨シンボルの自動選択
	useWarnAmountErrorSnackbar(); // 販売価格の数量が不正な場合の警告通知
	useWarnDecimalsErrorSnackbar(); // 販売価格の小数点以下桁数が不正な場合の警告通知

	return (
		<Placeholder
			icon={getBlockIcon()}
			label={'baywall'}
			instructions={t('select_network_category_and_price_message')}
			id="fd9e15e3-9f4f-4537-8470-3da48e66d6e9"
		>
			{/* エラー表示 */}
			<div style={{ width: '100%' }}>
				<ApiErrorNotification {...useApiErrorNotificationProps()} />
				<SettingsErrorNotification {...useSettingsErrorNotificationProps()} />
			</div>

			{/* 設定項目 ※管理画面のcss(.from-table)を流用 */}
			<table className="form-table">
				<tbody>
					{/* ネットワークカテゴリ設定 */}
					<tr>
						<th scope="row">{t('network_category_label')}:</th>
						<td>
							<div style={{ display: 'flex', alignItems: 'center', gap: '0.75em' }}>
								<SellingNetworkCategorySelect {...useSellingNetworkCategorySelectProps()} />
							</div>
						</td>
					</tr>

					{/* 販売価格設定 */}
					<tr>
						<th scope="row">{t('price_label')}:</th>
						<td>
							<div style={{ display: 'flex', alignItems: 'center', gap: '0.75em' }}>
								<SellingPriceAmount
									{...useSellingPriceAmountProps()}
									style={{ width: '135px', borderRadius: '3px' }}
								/>
								<SellingPriceSymbolSelect {...useSellingPriceSymbolSelectProps()} />
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</Placeholder>
	);
};
