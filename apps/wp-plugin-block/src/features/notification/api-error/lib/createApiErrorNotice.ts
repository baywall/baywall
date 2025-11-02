import { NoticeList } from '@wordpress/components';

type Notices = React.ComponentProps< typeof NoticeList >[ 'notices' ];

const API_ERROR_NOTICE_ID = 'cd890884-9b16-4b09-8d8b-435b71bea425'; // 適当なID

/**
 * \@wordpress/componentsのNoticeListに渡すAPIエラー通知を生成します
 *
 * @param error useQueryのerrorプロパティ
 */
export const createApiErrorNotice = ( error: unknown ): Notices[ number ] | null => {
	if ( ! error ) {
		return null;
	}

	return {
		id: API_ERROR_NOTICE_ID,
		status: 'error',
		isDismissible: false,
		content: String( error ),
	};
};
