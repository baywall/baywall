/** デバッグモードで動作しているかどうかを取得します */
export const isDebugMode = () => {
	// URLのクエリパラメータに`debug=true`または`debug=1`が含まれている場合はデバッグモードとする
	const urlParams = new URLSearchParams( window.location.search );
	return [ 'true', '1' ].includes( urlParams.get( 'debug' ) ?? '' );
};
