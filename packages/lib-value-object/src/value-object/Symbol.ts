import { ValueObject } from './base/ValueObject';

const brand: unique symbol = Symbol( 'Symbol' );

/** 通貨記号を表すvalue-object */
class TokenSymbol implements ValueObject< TokenSymbol > {
	/** 型区別用のフィールド */
	private [ brand ]!: void;

	public readonly value: string;

	private constructor( symbolValue: string ) {
		TokenSymbol.checkSymbol( symbolValue );
		this.value = symbolValue;
	}

	public static from( symbolValue: string ): TokenSymbol {
		return new TokenSymbol( symbolValue );
	}

	public equals( other: TokenSymbol ): boolean {
		return this.value === other.value;
	}

	public toString(): string {
		return this.value;
	}

	private static checkSymbol( symbolValue: string ): void {
		if ( ! TokenSymbol.isSymbol( symbolValue ) ) {
			throw new Error( `[7D19A592] Invalid symbol value: '${ symbolValue }'` );
		}
	}
	private static isSymbol( symbolValue: string ): boolean {
		return symbolValue.length > 0 && symbolValue.trim() === symbolValue;
	}
}

export { TokenSymbol as Symbol };
