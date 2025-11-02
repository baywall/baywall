import { useEffect } from 'react';
import { ScreenNotifier } from '../../../../lib/gutenberg/notification/ScreenNotifier';
import { useTranslation } from 'react-i18next';

export interface InvalidDecimalsNotificationProps {
	isError: boolean;
	screenNotifier: ScreenNotifier;
}

export const InvalidDecimalsNotification: React.FC< InvalidDecimalsNotificationProps > = ( props ) => {
	const { t } = useTranslation();
	const { isError, screenNotifier } = props;

	useEffect( () => {
		const id = '8bbdd02d-67ea-4b1b-8896-a32925dfca74'; // 通知に使用する一意のID（適当な値）

		if ( isError ) {
			screenNotifier.showWarnSnackbar( t( 'too_many_decimal_places_message' ), {
				id,
				isDismissible: false, // クリックで閉じない
				explicitDismiss: true, // 閉じるボタンを表示し、時間経過で閉じない
			} );
		} else {
			screenNotifier.hide( id );
		}

		// クリーンアップ時に通知を消す
		return () => screenNotifier.hide( id );
	}, [ t, isError, screenNotifier ] );

	// 通知はsnackbarを使って行うため、このコンポーネント自体は描画しない
	return null;
};
