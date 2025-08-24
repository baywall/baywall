export class ChainId {
	public readonly value: number;

	private constructor( value: number ) {
		ChainId.checkChainIdValue( value );
		this.value = value;
	}

	public static from( chainIdValue: number ): ChainId {
		return new ChainId( chainIdValue );
	}

	private static checkChainIdValue( chainIdValue: number ): void {
		if ( ! Number.isInteger( chainIdValue ) || chainIdValue <= 0 ) {
			throw new Error( '[8C8D83D1] ChainId must be a positive integer.' );
		}
	}
}
