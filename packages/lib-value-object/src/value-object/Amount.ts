export class Amount {
	public readonly value: string;

	// TODO: public -> private
	/**
	 * @param value
	 * @deprecated
	 */
	public constructor( value: string ) {
		Amount.checkAmountValue( value ); // フォーマットチェック
		this.value = value;
	}

	public static from( amountValue: string ): Amount {
		return new Amount( amountValue );
	}

	private static checkAmountValue( amountValue: string ): void {
		if ( ! Amount.isAmountValue( amountValue ) ) {
			throw new Error( `[9664AE79] Invalid amount value: '${ amountValue }'` );
		}
	}

	private static isAmountValue( amountValue: string ): boolean {
		// 整数または小数点を含む数字の文字列であるかどうかをチェック
		// 例: "123", "-123", "123.45", "-123.45"
		return /^-?\d+(\.\d+)?$/.test( amountValue );
	}
}
