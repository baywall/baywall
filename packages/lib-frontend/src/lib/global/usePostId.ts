import { useEffect, useState } from 'react';
import { usePhpVar } from './_usePhpVar';

/**
 * 投稿IDを取得します。
 */
export const usePostId = (): number | null | undefined => {
	const [ postId, setPostId ] = useState< number | null | undefined >( undefined );

	const phpVar = usePhpVar();

	useEffect( () => {
		if ( phpVar === null || phpVar.postId === null ) {
			setPostId( null );
		} else if ( typeof phpVar.postId === 'number' ) {
			setPostId( phpVar.postId );
		} else {
			throw new Error( '[CD24E7A4] Unknown Error. phpVar: ' + JSON.stringify( phpVar ) );
		}
	}, [ phpVar ] );

	return postId;
};
