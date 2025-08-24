export class ChainId {
	private readonly chainIdValue: number;

	private constructor( chainIdValue: number ) {
		ChainId.checkChainIdValue( chainIdValue );

		this.chainIdValue = chainIdValue;
	}

	public static from( chainIdValue: number ): ChainId {
		return new ChainId( chainIdValue );
	}

	public get value(): number {
		return this.chainIdValue;
	}

	private static checkChainIdValue( chainIdValue: number ): void {
		if ( ! Number.isInteger( chainIdValue ) || chainIdValue <= 0 ) {
			throw new Error( '[8C8D83D1] ChainId must be a positive integer.' );
		}
	}
}
