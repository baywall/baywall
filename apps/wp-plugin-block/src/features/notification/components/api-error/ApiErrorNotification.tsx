import { useMemo } from 'react';
import type { NoticeList as NoticeListType } from '@wordpress/components';
import { createApiErrorNotice } from '../../utils/createApiErrorNotice';
import { type Notices } from '../../../../shared/lib/notice';

const NoticeList = window.wp.components.NoticeList as typeof NoticeListType;

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
