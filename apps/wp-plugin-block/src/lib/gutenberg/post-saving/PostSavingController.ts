import { dispatch } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';

/**
 * 投稿の保存機能を制御するクラス
 *
 * lockを行うことによって得られる効果:
 *   - 公開済みの投稿の更新ができなくなる
 *   - 未公開の投稿の公開ができなくなる
 *
 * lockを行っても得られない効果:
 *   - 投稿の下書き保存（lockを行っても、下書き保存は可能）
 */
export class PostSavingController {
	/**
	 * 投稿の保存をロックします
	 * @param lockName
	 */
	public lock( lockName: string ) {
		dispatch( editorStore ).lockPostSaving( lockName );
	}

	/**
	 * 投稿の保存のロックを解除します
	 * @param lockName
	 */
	public unlock( lockName: string ) {
		dispatch( editorStore ).unlockPostSaving( lockName );
	}
}
