import { useMemo } from 'react';
import type { NoticeList as NoticeListType } from '@wordpress/components';
import { createApiErrorNotice } from './lib/createApiErrorNotice';

const NoticeList = window.wp.components.NoticeList as typeof NoticeListType;
type Notices = React.ComponentProps<typeof NoticeListType>['notices'];

export type ApiErrorNotificationProps = Omit<React.ComponentProps<typeof NoticeListType>, 'notices'> & {
	/** useQueryのerrorプロパティ */
	error: unknown;
};

/**
 * 初期データ取得時のAPIエラー通知
 * @param props
 */
export const ApiErrorNotification = (props: ApiErrorNotificationProps) => {
	const { error, ...rest } = props;

	const notice: Notices[number] | null = useMemo(() => {
		return createApiErrorNotice(error);
	}, [error]);

	return notice ? <NoticeList {...rest} notices={[notice]} /> : null;
};
