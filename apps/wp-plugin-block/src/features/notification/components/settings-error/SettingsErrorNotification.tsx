import { useMemo } from 'react';
import type { NoticeList as NoticeListType } from '@wordpress/components';
import { useTranslation } from 'react-i18next';
import { createSettingsErrorNotice } from '../../utils/createSettingsErrorNotice';
import { type Notices } from '../../../../shared/lib/notice';

const NoticeList = window.wp.components.NoticeList as typeof NoticeListType;

export type SettingsErrorNotificationProps = Omit<React.ComponentProps<typeof NoticeListType>, 'notices'> & {
	/** 設定が正しい場合はtrue */
	isSettingsComplete: boolean | undefined;
	/** 本プラグインのダッシュボードのURL */
	dashboardUrl: string;
};

/**
 * 設定が不正な場合に表示するエラー
 * @param props
 */
export const SettingsErrorNotification = (props: SettingsErrorNotificationProps) => {
	const { isSettingsComplete, dashboardUrl } = props;
	const { t } = useTranslation();

	const notices: Notices | undefined = useMemo(() => {
		const notice = createSettingsErrorNotice(isSettingsComplete, dashboardUrl, t);
		if (notice === undefined) {
			return undefined;
		} else if (notice === null) {
			return [];
		} else {
			return [notice];
		}
	}, [t, isSettingsComplete, dashboardUrl]);

	return notices && notices.length > 0 ? <NoticeList {...props} notices={notices} /> : null;
};
