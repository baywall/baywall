import { useEffect, useState } from 'react';
import { usePhpVar } from './_usePhpVar';

/**
 * 投稿IDを取得します。
 */
export const usePostID = (): number | null | undefined => {
	const [ postID, setPostID ] = useState< number | null | undefined >( undefined );

	const phpVar = usePhpVar();

	useEffect( () => {
		if ( phpVar === null || phpVar.postID === undefined ) {
			setPostID( null );
		} else if ( typeof phpVar.postID === 'number' ) {
			setPostID( phpVar.postID );
		} else {
			throw new Error( '[CD24E7A4] Unknown Error. phpVar: ' + JSON.stringify( phpVar ) );
		}
	}, [ phpVar ] );

	return postID;
};
