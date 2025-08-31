const brand: unique symbol = Symbol( 'ChainIdBrand' );

/** チェーンIDを表すvalue-object */
export class ChainId {
	/** 型区別用のフィールド */
	private [ brand ]!: void;

	public readonly value: number;

	private constructor( value: number ) {
		ChainId.checkChainIdValue( value );
		this.value = value;
	}

	public static from( chainIdValue: number ): ChainId {
		return new ChainId( chainIdValue );
	}

	public toString(): string {
		return `${ this.value }`;
	}

	public equals( other: ChainId ): boolean {
		return this.value === other.value;
	}

	private static checkChainIdValue( chainIdValue: number ): void {
		if ( ! Number.isInteger( chainIdValue ) || chainIdValue <= 0 ) {
			throw new Error(
				`[8C8D83D1] ChainId must be a positive integer. ${ chainIdValue } (${ typeof chainIdValue })`
			);
		}
	}
}
