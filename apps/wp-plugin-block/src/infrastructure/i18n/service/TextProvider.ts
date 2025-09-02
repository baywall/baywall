// TODO: i18n化
export class TextProvider {

	/** 読み込み中に表示する文字列 */
	public get loading(): string {
		return 'Loading...';
	}

	/** Selectの選択肢が存在しない場合に表示する文字列 */
	public get noOptionsAvailable(): string {
		return 'No options available';
	}
}
