import { NetworkCategoryId } from '@serendipity/lib-value-object';

const brand: unique symbol = Symbol( 'NetworkCategoryBrand' );

export class NetworkCategory {
	/** 型区別用のフィールド */
	// @ts-ignore: unused-variable
	private [ brand ]!: void;

	public readonly id: NetworkCategoryId;
	public readonly name: string;

	private constructor( id: NetworkCategoryId, name: string ) {
		this.id = id;
		this.name = name;
	}

	public static from( id: NetworkCategoryId, name: string ): NetworkCategory {
		return new NetworkCategory( id, name );
	}
}
