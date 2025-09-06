const brand: unique symbol = Symbol( 'AmountBrand' );

/** 数量を表すvalue-object */
export class Amount {
	/** 型区別用のフィールド */
	private [ brand ]!: void;

	public readonly value: string;

	private constructor( value: string ) {
		Amount.checkAmountValue( value );
		this.value = Amount.format( value );
	}

	public static from( amountValue: string ): Amount {
		return new Amount( amountValue );
	}

	public toString(): string {
		return this.value;
	}

	private static format( amountValue: string ): string {
		// 小数点がある場合
		if ( amountValue.includes( '.' ) ) {
			amountValue = amountValue
				.replace( /0+$/, '' ) // 末尾の0を削除
				.replace( /\.$/, '' ); // 末尾が小数点の場合、小数点を削除
		}
		return amountValue;
	}

	private static checkAmountValue( amountValue: string ): void {
		if ( ! Amount.isAmountValue( amountValue ) ) {
			throw new Error( `[9664AE79] Invalid amount value: '${ amountValue }'` );
		}
	}

	private static isAmountValue( amountValue: string ): boolean {
		// 整数または小数点を含む数字の文字列であるかどうかをチェック
		// 例: "123", "-123", "123.45", "-123.45"
		return /^-?(?:0|[1-9]\d*)(\.\d+)?$/.test( amountValue );
	}
}
