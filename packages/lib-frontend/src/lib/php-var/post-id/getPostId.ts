import { PostId } from '@serendipity/lib-value-object';
import { getPhpVar } from '../getPhpVar';

export const getPostId = (): PostId | null => {
	const phpVar = getPhpVar();

	if ( phpVar === null ) {
		return null; // HTMLにphpVarが出力されていない場合
	} else if ( phpVar.postId === undefined || phpVar.postId === null ) {
		return null; // 投稿ページ以外など、postIdが設定されていない場合
	} else if ( typeof phpVar.postId === 'number' ) {
		return PostId.from( phpVar.postId );
	} else {
		throw new Error( `[1D29B2A1] invalid postId: ${ phpVar.postId }, ${ typeof phpVar.postId }` );
	}
};
