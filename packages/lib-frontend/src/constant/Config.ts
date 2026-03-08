export class Config {
	// istanbul ignore next
	private constructor() {} // eslint-disable-line no-useless-constructor

	/**
	 * アプリケーションの識別子
	 *
	 * ※ ローカルストレージのキーなどで使用
	 */
	public static APP_ID = 'baywall';

	/** WordPressで使用しているテキストドメイン */
	public static readonly TEXT_DOMAIN = 'baywall';

	/** GraphQLのエンドポイントパス */
	public static readonly GRAPHQL_ENDPOINT_PATH = 'baywall/graphql';

	/**
	 * サーバーから出力されるグローバル変数の名前
	 *
	 * ※ REST API等の情報が格納される変数名
	 * ※ PHP側と整合性を取ること
	 */
	public static readonly PHP_VAR_NAME = 'php_var_20792bdd';

	/**
	 * 本プロジェクトのフロントエンド側で出力するログの日付フォーマット
	 */
	public static readonly LOG_DATE_FORMAT = 'yyyy-MM-dd HH:mm:ss.SSS';
}
