import { useMemo } from '@wordpress/element';
import { NoticeList } from '@wordpress/components';
import { createApiErrorNotice } from './lib/createApiErrorNotice';

type Notices = React.ComponentProps< typeof NoticeList >[ 'notices' ];

export type ApiErrorNotificationProps = Omit< React.ComponentProps< typeof NoticeList >, 'notices' > & {
	/** useQueryのerrorプロパティ */
	error: unknown;
};

/**
 * 初期データ取得時のAPIエラー通知
 * @param props
 */
export const ApiErrorNotification = ( props: ApiErrorNotificationProps ) => {
	const { error, ...rest } = props;

	const notice: Notices[ number ] | null = useMemo( () => {
		return createApiErrorNotice( error );
	}, [ error ] );

	return notice ? <NoticeList { ...rest } notices={ [ notice ] } /> : null;
};
