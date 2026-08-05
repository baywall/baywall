import { dispatch } from '@wordpress/data';

/**
 * 投稿エディターストア。
 *
 * 'core/editor' ストアの型付き `store` オブジェクトではなく文字列を直接使用します。
 * 型付きオブジェクトを提供するパッケージから直接 import すると、mediabunny (MPL-2.0) を含む
 * 依存ツリー(upload-media → video-conversion 経由)が pnpm の本番依存ツリーに
 * 引き込まれるため、それを回避するのが目的です。
 * 詳細な経緯は package.json の "# note" の該当コメントを参照してください。
 */
const EDITOR_STORE_NAME = 'core/editor';

/**
 * 'core/editor' ストアで本クラスが使用するアクションの型定義
 *
 * dispatch(文字列) の戻り値型が unknown になるため、
 * any ではなく最小限の型を定義してキャストします。
 */
interface EditorStoreActions {
	lockPostSaving: (lockName: string) => void;
	unlockPostSaving: (lockName: string) => void;
}

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
	public lock(lockName: string) {
		(dispatch(EDITOR_STORE_NAME) as EditorStoreActions).lockPostSaving(lockName);
	}

	/**
	 * 投稿の保存のロックを解除します
	 * @param lockName
	 */
	public unlock(lockName: string) {
		(dispatch(EDITOR_STORE_NAME) as EditorStoreActions).unlockPostSaving(lockName);
	}
}
