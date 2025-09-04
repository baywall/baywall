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

	/** 販売価格の数量の入力が不正な場合に表示するメッセージ */
	public get invalidPriceAmountMessage(): string {
		return 'Invalid price amount. Please enter a valid value.';
	}

	/** 管理画面の設定で、未登録の項目がある場合に表示するメッセージ */
	public get settingsIncomplete(): string {
		// `一部の設定がまだ完了していません。`
		return 'Some settings are not yet configured.';
	}

	/** ダッシュボード */
	public get dashboard(): string {
		return 'Dashboard';
	}
}
