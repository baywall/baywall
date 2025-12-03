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

	/** @deprecated */
	public get isMainnet(): boolean {
		return this.equals( NetworkCategoryId.Mainnet );
	}
	/** @deprecated */
	public get isTestnet(): boolean {
		return this.equals( NetworkCategoryId.Testnet );
	}
	/** @deprecated */
	public get isPrivatenet(): boolean {
		return this.equals( NetworkCategoryId.Privatenet );
	}

	/** @deprecated メインネットのネットワークカテゴリIDを表すインスタンスを取得します */
	public static get Mainnet(): NetworkCategoryId {
		return NetworkCategoryId.from( NetworkCategoryIdValue.Mainnet );
	}
	/** @deprecated テストネットのネットワークカテゴリIDを表すインスタンスを取得します */
	public static get Testnet(): NetworkCategoryId {
		return NetworkCategoryId.from( NetworkCategoryIdValue.Testnet );
	}
	/** @deprecated プライベートネットのネットワークカテゴリIDを表すインスタンスを取得します */
	public static get Privatenet(): NetworkCategoryId {
		return NetworkCategoryId.from( NetworkCategoryIdValue.Privatenet );
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
