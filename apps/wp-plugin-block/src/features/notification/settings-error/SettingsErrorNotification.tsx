import { useMemo } from '@wordpress/element';
import { NoticeList } from '@wordpress/components';
import { UrlProvider } from '../../../lib/url/UrlProvider';
import { useTranslation } from 'react-i18next';
import { createSettingsErrorNotice } from './lib/createSettingsErrorNotice';

type Notices = React.ComponentProps< typeof NoticeList >[ 'notices' ];

export type SettingsErrorNotificationProps = Omit< React.ComponentProps< typeof NoticeList >, 'notices' > & {
	/** 設定が正しい場合はtrue */
	isSettingsComplete: boolean | undefined;
	urlProvider: UrlProvider;
};

/**
 * 設定が不正な場合に表示するエラー
 * @param props
 */
export const SettingsErrorNotification: React.FC< SettingsErrorNotificationProps > = ( props ) => {
	const { isSettingsComplete, urlProvider } = props;
	const { t } = useTranslation();

	const notices: Notices | undefined = useMemo( () => {
		const notice = createSettingsErrorNotice( isSettingsComplete, urlProvider.dashboard.toString(), t );
		if ( notice === undefined ) {
			return undefined;
		} else if ( notice === null ) {
			return [];
		} else {
			return [ notice ];
		}
	}, [ t, isSettingsComplete, urlProvider ] );

	return notices && notices.length > 0 ? <NoticeList { ...props } notices={ notices } /> : null;
};
