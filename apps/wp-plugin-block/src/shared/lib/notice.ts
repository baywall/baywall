import type { NoticeList } from '@wordpress/components';

/** \@wordpress/components の NoticeList に渡す通知一覧の型 */
export type Notices = React.ComponentProps<typeof NoticeList>['notices'];

/**
 * エラー通知(notice)を生成します
 *
 * @param id      通知の一意なID
 * @param content 通知本文
 * @param actions 通知に表示するアクション
 */
export const createErrorNotice = (
	id: string,
	content: string,
	actions?: Notices[number]['actions']
): Notices[number] => {
	return {
		id,
		status: 'error',
		isDismissible: false,
		content,
		...(actions ? { actions } : {}),
	};
};
