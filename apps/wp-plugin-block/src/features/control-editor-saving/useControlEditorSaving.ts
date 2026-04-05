import { useEffect } from '@wordpress/element';
import { PostSavingController } from '../../lib/gutenberg/post-saving/PostSavingController';
import { useShouldLockEditorSaving } from './hooks/useShouldLockEditorSaving';

/**
 * 投稿編集画面の保存処理の有効/無効を制御します
 *
 * ※ 下書き保存の制御はできません。@see PostSavingController
 * ※ 公開済み投稿の自動保存は制御していません。（履歴が増えるだけで特に影響はないと判断）
 */
export const useControlEditorSaving = () => {
	const shouldLock = useShouldLockEditorSaving();

	useEffect( () => {
		const lockName = '38f6569d-91bb-424d-8e94-4f7e35c64edd'; // 適当なロック名
		const postSavingController = new PostSavingController();
		if ( shouldLock ) {
			postSavingController.lock( lockName );
		} else {
			postSavingController.unlock( lockName );
		}
		return () => {
			// クリーンアップ時にロックを解除
			postSavingController.unlock( lockName );
		};
	}, [ shouldLock ] );
};
