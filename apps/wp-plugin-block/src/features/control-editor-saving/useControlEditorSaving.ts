import { useEffect, useMemo, useState } from '@wordpress/element';
import { useBlockEditProps } from '../../provider/block-edit-props/useBlockEditProps';
import { PostSavingController } from '../../lib/editor/post-saving/PostSavingController';

/**
 * 投稿編集画面の保存処理の有効/無効を制御します
 *
 * ※ 下書き保存の制御はできません。@see PostSavingController
 * ※ 公開済み投稿の自動保存は制御していません。（履歴が増えるだけで特に影響はないと判断）
 */
export const useControlEditorSaving = () => {
	const { attributes } = useBlockEditProps();
	const [ lock, setLock ] = useState( false ); // 保存ロック状態を管理
	const postSavingController = useMemo( () => new PostSavingController(), [] );

	useEffect( () => {
		// ブロックの属性にnullが含まれている場合、保存をロックする
		const includesNull = Object.values( attributes ).some( ( value ) => value === null );
		setLock( includesNull );
	}, [ attributes, setLock ] );

	useEffect( () => {
		const lockName = '38f6569d-91bb-424d-8e94-4f7e35c64edd'; // 適当なロック名
		if ( lock ) {
			postSavingController.lock( lockName );
		} else {
			postSavingController.unlock( lockName );
		}
		return () => {
			// クリーンアップ時にロックを解除
			postSavingController.unlock( lockName );
		};
	}, [ lock, postSavingController ] );
};
