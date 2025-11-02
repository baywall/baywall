import { useMemo } from '@wordpress/element';
import { NoticeList } from '@wordpress/components';
import { useBlockInitDataQuery } from '../../../query/useBlockInitDataQuery';
import { UrlProvider } from '../../../lib/url/UrlProvider';
import { useTranslation } from 'react-i18next';

type Notices = React.ComponentProps< typeof NoticeList >[ 'notices' ];

type SettingsErrorNotificationProps = Omit< React.ComponentProps< typeof NoticeList >, 'notices' >;

/**
 * 設定が不正な場合に表示するエラー
 * @param props
 */
export const SettingsErrorNotification: React.FC< SettingsErrorNotificationProps > = ( props ) => {
	const { t } = useTranslation();
	const { data } = useBlockInitDataQuery();

	const notices: Notices = useMemo( () => {
		if ( data === undefined ) {
			// データ取得中は何も表示しない
			return [];
		} else if ( data.sellableNetworkCategories.length > 0 ) {
			// 設定が正しい場合は何も表示しない
			return [];
		}

		const urlProvider = new UrlProvider();
		const notice: Notices[ number ] = {
			id: 'a7733ff3-ad6a-4d34-bba7-1b1d49b28fb2', // 適当なID
			status: 'error',
			isDismissible: false,
			content: t( 'settings_incomplete_message' ),
			actions: [
				{
					label: t( 'dashboard_label' ),
					url: urlProvider.dashboard.toString(),
				},
			],
		};
		return [ notice ];
	}, [ t, data ] );

	return <NoticeList { ...props } notices={ notices } />;
};
