import { Decimals, NetworkCategoryId, Symbol, ValueObject } from '@serendipity/lib-value-object';
import { brand } from './TokenBrand';

export class Token implements ValueObject< Token > {
	/** 型区別用のフィールド */
	// @ts-ignore: unused-variable
	private [ brand ]!: void;

	// eslint-disable-next-line no-useless-constructor
	private constructor(
		public readonly networkCategoryId: NetworkCategoryId,
		public readonly symbol: Symbol,
		public readonly decimals: Decimals
	) {}

	public static from( networkCategoryId: NetworkCategoryId, symbol: Symbol, decimals: Decimals ): Token {
		return new Token( networkCategoryId, symbol, decimals );
	}

	public equals( other: Token ): boolean {
		return this.networkCategoryId.equals( other.networkCategoryId ) && this.symbol.equals( other.symbol );
	}
}
