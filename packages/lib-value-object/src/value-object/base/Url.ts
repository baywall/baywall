/**
 * URLを表す抽象クラス
 */
export abstract class Url {
	private readonly urlValue: string;

	public constructor( urlValue: string ) {
		Url.checkUrl( urlValue ); // URLの形式をチェック

		this.urlValue = urlValue;
	}

	/** URLを文字列で取得します */
	public get value(): string {
		return this.urlValue;
	}

	/**
	 * 指定した文字列がURLの形式かどうかを返します
	 * @param urlValue
	 */
	private static isUrl( urlValue: string ): boolean {
		try {
			new URL( urlValue );
			return true;
		} catch ( _ ) {
			return false;
		}
	}

	private static checkUrl( urlValue: string ): void {
		if ( ! Url.isUrl( urlValue ) ) {
			throw new Error( `[2A3EF522] Invalid url: ${ urlValue }` );
		}
	}
}
