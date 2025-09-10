import { Config } from '../../constant/Config';

/** サーバーから出力されるグローバル変数の名前を取得するクラス */
export class PhpVarNameProvider {
	public get(): string {
		return Config.PHP_VAR_NAME;
	}
}
