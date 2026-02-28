import { NetworkCategoryId, Symbol as TokenSymbol } from '@serendipity/lib-value-object';

const brand: unique symbol = Symbol( 'NetworkCategory' );

export class NetworkCategory {
	/** 型区別用のフィールド */
	// @ts-ignore: unused-variable
	private [ brand ]!: void;

	public readonly id: NetworkCategoryId;
	public readonly name: string;
	public readonly sellableSymbols: TokenSymbol[];

	private constructor( id: NetworkCategoryId, name: string, sellableSymbols: TokenSymbol[] ) {
		this.id = id;
		this.name = name;
		this.sellableSymbols = sellableSymbols;
	}

	public static from( id: NetworkCategoryId, name: string, sellableSymbols: TokenSymbol[] ): NetworkCategory {
		return new NetworkCategory( id, name, sellableSymbols );
	}
}
