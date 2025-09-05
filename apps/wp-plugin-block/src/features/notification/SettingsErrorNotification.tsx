import { useMemo } from '@wordpress/element';
import { NoticeList } from '@wordpress/components';
import { TextProvider } from '../../lib/i18n/TextProvider';
import { useBlockInitDataQuery } from '../../query/useBlockInitDataQuery';
import { UrlProvider } from '../../lib/url/UrlProvider';

type Notices = React.ComponentProps< typeof NoticeList >[ 'notices' ];

type SettingsErrorNotificationProps = Omit< React.ComponentProps< typeof NoticeList >, 'notices' >;

/**
 * 設定が不正な場合に表示するエラー
 * @param props
 */
export const SettingsErrorNotification: React.FC< SettingsErrorNotificationProps > = ( props ) => {
	const { data } = useBlockInitDataQuery();

	const notices: Notices = useMemo( () => {
		if ( data === undefined ) {
			// データ取得中は何も表示しない
			return [];
		} else if ( data.sellableNetworkCategoryIds.length > 0 && data.sellableCurrencies.length > 0 ) {
			// 設定が正しい場合は何も表示しない
			return [];
		}

		const textProvider = new TextProvider();
		const urlProvider = new UrlProvider();
		const notice: Notices[ number ] = {
			id: 'a7733ff3-ad6a-4d34-bba7-1b1d49b28fb2', // 適当なID
			status: 'error',
			isDismissible: false,
			content: textProvider.settingsIncomplete,
			actions: [
				{
					label: textProvider.dashboard,
					url: urlProvider.dashboard.toString(),
				},
			],
		};
		return [ notice ];
	}, [ data ] );

	return <NoticeList { ...props } notices={ notices } />;
};
