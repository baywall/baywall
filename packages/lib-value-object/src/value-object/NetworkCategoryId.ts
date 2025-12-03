import { ValueObject } from './base/ValueObject';

const brand: unique symbol = Symbol( 'NetworkCategoryId' );

/** ネットワークカテゴリIDを表すvalue-object */
export class NetworkCategoryId implements ValueObject< NetworkCategoryId > {
	/** 型区別用のフィールド */
	private [ brand ]!: void;

	public readonly value: number;

	private constructor( NetworkCategoryIdValue: number ) {
		NetworkCategoryId.checkNetworkCategoryIdValue( NetworkCategoryIdValue );
		this.value = NetworkCategoryIdValue;
	}

	public static from( networkCategoryIdValue: number ): NetworkCategoryId {
		return new NetworkCategoryId( networkCategoryIdValue );
	}

	public toString(): string {
		return `${ this.value }`;
	}

	public equals( other: NetworkCategoryId ): boolean {
		return this.value === other.value;
	}

	private static checkNetworkCategoryIdValue( networkCategoryIdValue: number ): void {
		if ( ! NetworkCategoryId.isNetworkCategoryIdValue( networkCategoryIdValue ) ) {
			throw new Error( `[E77679FC] Invalid network category ID: ${ networkCategoryIdValue }` );
		}
	}
	private static isNetworkCategoryIdValue( networkCategoryIdValue: number ): boolean {
		return ( Object.values( NetworkCategoryIdValue ) as number[] ).includes( networkCategoryIdValue );
	}
}

const NetworkCategoryIdValue = {
	Mainnet: 1,
	Testnet: 2,
	Privatenet: 3,
} as const;
type NetworkCategoryIdValue = ( typeof NetworkCategoryIdValue )[ keyof typeof NetworkCategoryIdValue ];
