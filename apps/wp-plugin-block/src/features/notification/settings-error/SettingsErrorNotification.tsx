import { useMemo } from 'react';
import type { NoticeList as NoticeListType } from '@wordpress/components';
import { UrlProvider } from '../../../lib/url/UrlProvider';
import { useTranslation } from 'react-i18next';
import { createSettingsErrorNotice } from './lib/createSettingsErrorNotice';

const NoticeList = window.wp.components.NoticeList as typeof NoticeListType;
type Notices = React.ComponentProps<typeof NoticeListType>['notices'];

export type SettingsErrorNotificationProps = Omit<React.ComponentProps<typeof NoticeListType>, 'notices'> & {
	/** 設定が正しい場合はtrue */
	isSettingsComplete: boolean | undefined;
	urlProvider: UrlProvider;
};

/**
 * 設定が不正な場合に表示するエラー
 * @param props
 */
export const SettingsErrorNotification = (props: SettingsErrorNotificationProps) => {
	const { isSettingsComplete, urlProvider } = props;
	const { t } = useTranslation();

	const notices: Notices | undefined = useMemo(() => {
		const notice = createSettingsErrorNotice(isSettingsComplete, urlProvider.dashboard.toString(), t);
		if (notice === undefined) {
			return undefined;
		} else if (notice === null) {
			return [];
		} else {
			return [notice];
		}
	}, [t, isSettingsComplete, urlProvider]);

	return notices && notices.length > 0 ? <NoticeList {...props} notices={notices} /> : null;
};
