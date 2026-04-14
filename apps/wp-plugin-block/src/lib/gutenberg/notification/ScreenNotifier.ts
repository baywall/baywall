import { dispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
// import { WPNoticeAction } from '@wordpress/notices/build-types/store/actions';

/** 通知を画面に表示するクラス */
export class ScreenNotifier {
	/**
	 * 画面上部にエラーメッセージを表示します。
	 * @param message
	 * @param option
	 */
	public showError( message: string, option?: NoticeOption ) {
		this.show( NOTICE_STATUS.ERROR, message, option );
	}

	/**
	 * 画面下部にスナックバー形式でエラーメッセージを表示します。
	 * @param message
	 * @param option
	 */
	public showErrorSnackbar( message: string, option?: NoticeSnackbarOption ) {
		this.showSnackbar( NOTICE_STATUS.ERROR, message, option );
	}
	/**
	 * 画面下部にスナックバー形式で警告メッセージを表示します。
	 * @param message
	 * @param option
	 */
	public showWarnSnackbar( message: string, option?: NoticeSnackbarOption ) {
		this.showSnackbar( NOTICE_STATUS.WARNING, message, option );
	}

	private show( status: NoticeStatus, message: string, option?: NoticeOption ) {
		dispatch( noticesStore ).createNotice( status, message, {
			...option,
			type: 'default',
		} );
	}

	private showSnackbar( status: NoticeStatus, message: string, option?: NoticeSnackbarOption ) {
		const { icon, ...rest } = option || {};
		dispatch( noticesStore ).createNotice( status, message, {
			...rest,
			type: 'snackbar',
			icon: this.defaultSnackbarIcon( status, icon ),
		} );
	}

	/**
	 * 既定のsnackbarアイコンを取得します
	 * @param status
	 * @param icon
	 */
	private defaultSnackbarIcon( status: NoticeStatus, icon: string | null | undefined ): string | undefined {
		if ( typeof icon === 'string' ) {
			// 文字列が指定された場合、そのまま使用
			return icon;
		} else if ( icon === null ) {
			// アイコンを表示しない指定がされた時はundefinedを返す
			return undefined;
		} else if ( icon === undefined ) {
			// アイコンが指定されなかった場合、ステータスに応じた既定のアイコンを返す
			switch ( status ) {
				case NOTICE_STATUS.SUCCESS:
					return '✅';
				case NOTICE_STATUS.INFO:
					return 'ℹ️';
				case NOTICE_STATUS.ERROR:
					return '❌';
				case NOTICE_STATUS.WARNING:
					return '⚠️';
				default:
					return status satisfies never; // ここは通らない
			}
		} else {
			// ここは通らない
			throw new Error( `[E206D743] Unsupported icon value: ${ icon } (${ typeof icon })` );
		}
	}

	public hide( id: string, context?: string ) {
		dispatch( noticesStore ).removeNotice( id, context );
	}
}

const NOTICE_STATUS = {
	SUCCESS: 'success',
	INFO: 'info',
	ERROR: 'error',
	WARNING: 'warning',
} as const;
type NoticeStatus = ( typeof NOTICE_STATUS )[ keyof typeof NOTICE_STATUS ];

interface WPNoticeOption {
	context?: string | undefined;
	/**
	 * 通知の一意なID
	 *
	 * ※ 同じIDを指定することで重複して表示されることを防ぐことができます
	 */
	id?: string | undefined;
	isDismissible?: boolean | undefined;
	type?: 'default' | 'snackbar' | undefined;
	speak?: boolean | undefined;
	// actions?: WPNoticeAction[] | undefined;
	/** 表示するアイコン（絵文字） */
	icon?: string | undefined; // Only for type: 'snackbar'
	explicitDismiss?: boolean | undefined; // Only for type: 'snackbar'
	onDismiss?: ( () => void ) | undefined;
}

/** 画面上部に表示する通知のオプション */
export interface NoticeOption extends Omit< WPNoticeOption, 'type' | 'icon' | 'explicitDismiss' > {}

/** 画面下部にスナックバー形式で表示する通知のオプション */
export interface NoticeSnackbarOption extends Omit< WPNoticeOption, 'type' | 'icon' > {
	/** スナックバーに表示するアイコン。undefinedの場合、既定のアイコンが使用されます。アイコンを表示しない場合はnullを指定します。 */
	icon?: string | null | undefined;
}
