import { useMemo } from 'react';

export const usePostIdFromDom = () => {
	return useMemo( () => getPostIdFromDom(), [] );
};

/**
 * DOMから投稿IDを取得します。
 */
const getPostIdFromDom = () => {
	const postIdElement = document.getElementById( 'post_ID' );
	return postIdElement ? parseInt( ( postIdElement as HTMLInputElement ).value, 10 ) : null;
};
