import { NetworkCategoryId } from '@serendipity/lib-value-object';

const brand: unique symbol = Symbol( 'NetworkCategory' );

export class NetworkCategory {
	/** 型区別用のフィールド */
	// @ts-ignore: unused-variable
	private [ brand ]!: void;

	private constructor(
		public readonly id: NetworkCategoryId,
		public readonly name: string
	) {}

	public static from( id: NetworkCategoryId, name: string ): NetworkCategory {
		return new NetworkCategory( id, name );
	}
}
