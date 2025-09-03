// TODO: i18n化
export class TextProvider {

	/** 読み込み中に表示する文字列 */
	public get loading(): string {
		return 'Loading...';
	}

	/** 販売ネットワークカテゴリが選択されていない場合に表示する文字列 */
	public get selectSellingNetworkCategory(): string {
		// `販売ネットワークカテゴリを選択してください`
		return 'Select a selling network category';
	}

	/** Selectの選択肢が存在しない場合に表示する文字列 */
	public get noOptionsAvailable(): string {
		// `選択可能なオプションが存在しません`
		return 'No options available';
	}
}
