import { createErrorNotice, type Notices } from '../../../shared/lib/notice';

const API_ERROR_NOTICE_ID = 'cd890884-9b16-4b09-8d8b-435b71bea425'; // 適当なID

/**
 * \@wordpress/componentsのNoticeListに渡すAPIエラー通知を生成します
 *
 * @param error useQueryのerrorプロパティ
 */
export const createApiErrorNotice = (error: unknown): Notices[number] | null => {
	if (!error) {
		return null;
	}

	return createErrorNotice(API_ERROR_NOTICE_ID, String(error));
};
