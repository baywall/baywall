import { useMemo } from '@wordpress/element';
import { NoticeList } from '@wordpress/components';
import { UrlProvider } from '../../../lib/url/UrlProvider';
import { useTranslation } from 'react-i18next';

type Notices = React.ComponentProps< typeof NoticeList >[ 'notices' ];

export type SettingsErrorNotificationProps = Omit< React.ComponentProps< typeof NoticeList >, 'notices' > & {
	/** 設定が正しい場合はtrue */
	isSettingsComplete: boolean | undefined;
};

/**
 * 設定が不正な場合に表示するエラー
 * @param props
 */
export const SettingsErrorNotification: React.FC< SettingsErrorNotificationProps > = ( props ) => {
	const { isSettingsComplete } = props;
	const { t } = useTranslation();

	const notices: Notices | undefined = useMemo( () => {
		if ( isSettingsComplete === undefined ) {
			return undefined;
		} else if ( isSettingsComplete === true ) {
			return []; // 設定が正しい場合は通知項目なし
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
	}, [ t, isSettingsComplete ] );

	return notices && notices.length > 0 ? <NoticeList { ...props } notices={ notices } /> : null;
};
