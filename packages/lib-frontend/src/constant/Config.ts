export class Config {
	private constructor() {} // eslint-disable-line no-useless-constructor

	/**
	 * サーバーから出力されるグローバル変数の名前
	 *
	 * ※ REST API等の情報が格納される変数名
	 * ※ PHP側と整合性を取ること
	 */
	public static readonly PHP_VAR_NAME = 'php_var_20792bdd';
}
