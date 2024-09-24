import { useEffect, useState } from 'react';
import { usePhpVar } from './_usePhpVar';

/**
 * 投稿IDを取得します。
 */
export const usePostID = (): number | null | undefined => {
	const [ postID, setPostID ] = useState< number | null | undefined >( undefined );

	const restPhpVar = usePhpVar();

	useEffect( () => {
		if ( restPhpVar === null || restPhpVar.postID === undefined ) {
			setPostID( null );
		} else if ( typeof restPhpVar.postID === 'number' ) {
			setPostID( restPhpVar.postID );
		} else {
			throw new Error( '[CD24E7A4] Unkown Error. restPhpVar: ' + JSON.stringify( restPhpVar ) );
		}
	}, [ restPhpVar ] );

	return postID;
};
