import { useEffect } from '@wordpress/element';
import { ScreenNotifier } from '../../lib/gutenberg/notification/ScreenNotifier';
import { TextProvider } from '../../lib/i18n/TextProvider';

export interface AmountErrorNotificationProps {
	/** 入力された販売価格の数量の文字列が不正な場合はtrue */
	isError: boolean;
	screenNotifier: ScreenNotifier;
	textProvider: TextProvider;
}

/**
 * 販売価格の数量の入力が不正な場合にエラーを通知（表示）するコンポーネント
 * @param props
 */
export const AmountErrorNotification: React.FC< AmountErrorNotificationProps > = ( props ) => {
	const { isError, screenNotifier, textProvider } = props;

	useEffect( () => {
		const id = '8d75df90-3d6b-44c0-a690-19d861b0431c'; // 通知に使用する一意のID（適当な値）

		if ( isError ) {
			screenNotifier.showWarnSnackbar( textProvider.invalidPriceAmountMessage, {
				id,
				isDismissible: false, // クリックで閉じない
				explicitDismiss: true, // 閉じるボタンを表示し、時間経過で閉じない
			} );
		} else {
			screenNotifier.hide( id );
		}

		// クリーンアップ時に通知を消す
		return () => screenNotifier.hide( id );
	}, [ isError, screenNotifier, textProvider ] );

	return null; // 通知はsnackbarを使って行うため、このコンポーネント自体は描画しない
};
