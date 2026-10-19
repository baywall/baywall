import { useEffect, useMemo } from 'react';
import { ScreenNotifier } from './ScreenNotifier';

/**
 * 警告スナックバーの表示・非表示を制御します
 *
 * @param params
 * @param params.id        通知に使用する一意なID
 * @param params.message   通知文言
 * @param params.isVisible trueの場合は警告を表示し、falseの場合は非表示にする
 */
export const useWarnSnackbar = (params: { id: string; message: string; isVisible: boolean }): void => {
	const { id, message, isVisible } = params;
	const screenNotifier = useMemo(() => new ScreenNotifier(), []);

	useEffect(() => {
		if (isVisible) {
			screenNotifier.showWarnSnackbar(message, {
				id,
				isDismissible: false, // クリックで閉じない
				explicitDismiss: true, // 閉じるボタンを表示し、時間経過で閉じない
			});
		} else {
			screenNotifier.hide(id);
		}

		// クリーンアップ時に通知を消す
		return () => screenNotifier.hide(id);
	}, [id, message, isVisible, screenNotifier]);
};
