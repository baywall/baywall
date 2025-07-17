export class Amount {
	public constructor( amountValue: string ) {
		if ( ! isAmountValue( amountValue ) ) {
			throw new Error( `[9664AE79] Invalid amount value: '${ amountValue }'` );
		}
		this.amountValue = amountValue;
	}
	private readonly amountValue: string;

	public get value(): string {
		return this.amountValue;
	}
}

const isAmountValue = ( amountValue: string ): boolean => {
	// 整数または小数点を含む数字の文字列であるかどうかをチェック
	// 例: "123", "-123", "123.45", "-123.45"
	return /^-?\d+(\.\d+)?$/.test( amountValue );
};
