import { Config } from '../../constant/Config';

export class ClassNameProvider {
	/** ブロックの要素に指定するCSSのクラス名 */
	public get block(): string {
		return Config.BLOCK_CLASS_NAME;
	}
}
