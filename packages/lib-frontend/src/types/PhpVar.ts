export type PhpVar = {
	wpRestNonce: string;
	apiRoot: string;
	postId?: number | null;
	/** Shadow DOM 内に注入するウィジェットCSSのURL（バージョンクエリ付き）。未出力の場合は存在しない */
	viewCssUrl?: string;
};
