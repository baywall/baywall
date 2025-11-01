import { useEffect } from 'react';
import { ScreenNotifier } from '../../../../lib/gutenberg/notification/ScreenNotifier';
import { TextProvider } from '../../../../lib/i18n/TextProvider';

export interface InvalidDecimalsNotificationProps {
	isError: boolean;
	screenNotifier: ScreenNotifier;
	textProvider: TextProvider;
}

export const InvalidDecimalsNotification: React.FC< InvalidDecimalsNotificationProps > = ( props ) => {
	const { isError, screenNotifier, textProvider } = props;

	useEffect( () => {
		const id = '8bbdd02d-67ea-4b1b-8896-a32925dfca74'; // 通知に使用する一意のID（適当な値）

		if ( isError ) {
			screenNotifier.showWarnSnackbar( textProvider.invalidPriceAmountDecimalsMessage, {
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

	// 通知はsnackbarを使って行うため、このコンポーネント自体は描画しない
	return null;
};
