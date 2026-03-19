import { Decimals, NetworkCategoryId, Symbol as TokenSymbol, ValueObject } from '@serendipity/lib-value-object';

const brand: unique symbol = Symbol( 'Token' );

export class Token implements ValueObject< Token > {
	/** 型区別用のフィールド */
	// @ts-ignore: unused-variable
	private [ brand ]!: void;

	private constructor(
		public readonly networkCategoryId: NetworkCategoryId,
		public readonly symbol: TokenSymbol,
		public readonly decimals: Decimals
	) {}

	public static from( networkCategoryId: NetworkCategoryId, symbol: TokenSymbol, decimals: Decimals ): Token {
		return new Token( networkCategoryId, symbol, decimals );
	}

	public equals( other: Token ): boolean {
		return this.networkCategoryId.equals( other.networkCategoryId ) && this.symbol.equals( other.symbol );
	}
}
