import { NetworkCategoryId, Symbol } from '@serendipity/lib-value-object';
import { brand } from './NetworkCategoryBrand';

export class NetworkCategory {
	/** 型区別用のフィールド */
	// @ts-ignore: unused-variable
	private [ brand ]!: void;

	public readonly id: NetworkCategoryId;
	public readonly name: string;
	public readonly sellableSymbols: Symbol[];

	private constructor( id: NetworkCategoryId, name: string, sellableSymbols: Symbol[] ) {
		this.id = id;
		this.name = name;
		this.sellableSymbols = sellableSymbols;
	}

	public static from( id: NetworkCategoryId, name: string, sellableSymbols: Symbol[] ): NetworkCategory {
		return new NetworkCategory( id, name, sellableSymbols );
	}
}
