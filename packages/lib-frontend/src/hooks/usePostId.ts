import { useMemo } from 'react';
import { getPhpVar } from '../lib/php-var/getPhpVar';

/** 投稿IDを取得します。 */
export const usePostId = (): number | null | undefined => {
	return useMemo( () => {
		const phpVar = getPhpVar();

		if ( phpVar === null || phpVar.postId === null ) {
			return null;
		} else if ( typeof phpVar.postId === 'number' ) {
			return phpVar.postId;
		}
		throw new Error( '[1148EEBD] Invalid postId value.' + JSON.stringify( phpVar ) );
	}, [] );
};
