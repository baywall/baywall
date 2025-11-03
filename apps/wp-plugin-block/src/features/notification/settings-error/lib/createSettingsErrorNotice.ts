import { NoticeList } from '@wordpress/components';

type Notices = React.ComponentProps< typeof NoticeList >[ 'notices' ];

const SETTINGS_ERROR_NOTICE_ID = 'a7733ff3-ad6a-4d34-bba7-1b1d49b28fb2'; // 適当なID

/**
 * \@wordpress/componentsのNoticeListに渡す設定エラー通知を生成します
 *
 * @param isSettingsComplete 管理画面の設定が完了している場合はtrue、未完了の場合はfalse、未確認の場合はundefined
 * @param dashboardUrl
 * @param t                  useTranslation().t
 */
export const createSettingsErrorNotice = (
	isSettingsComplete: boolean | undefined,
	dashboardUrl: string,
	t: ( key: string ) => string
): Notices[ number ] | null | undefined => {
	if ( isSettingsComplete === undefined ) {
		return undefined;
	} else if ( isSettingsComplete === true ) {
		return null; // 設定が正しい場合は通知項目なし
	}

	return {
		id: SETTINGS_ERROR_NOTICE_ID,
		status: 'error',
		isDismissible: false,
		content: t( 'settings_incomplete_message' ),
		actions: [
			{
				label: t( 'dashboard_label' ),
				url: dashboardUrl,
			},
		],
	};
};
