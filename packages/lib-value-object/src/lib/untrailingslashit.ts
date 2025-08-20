/**
 * URL文字列から末尾のスラッシュを削除します。
 * @param url
 */
export const untrailingslashit = ( url: string ): string => {
	return url.replace( /\/+$/, '' );
};
