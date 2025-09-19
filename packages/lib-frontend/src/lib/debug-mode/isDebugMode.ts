/** デバッグモードで動作しているかどうかを取得します */
export const isDebugMode = () => {
	// URLのクエリパラメータに`debug=true`が含まれている場合はデバッグモードとする
	const urlParams = new URLSearchParams( window.location.search );
	return urlParams.get( 'debug' ) === 'true';
};
