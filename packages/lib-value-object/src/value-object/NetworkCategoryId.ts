export class NetworkCategoryId {
	public readonly value: number;

	public constructor( NetworkCategoryIdValue: number ) {
		NetworkCategoryId.checkNetworkCategoryIdValue( NetworkCategoryIdValue );
		this.value = NetworkCategoryIdValue;
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
		return new NetworkCategoryId( NetworkCategoryIdValue.Mainnet );
	}
	/** @deprecated テストネットのネットワークカテゴリIDを表すインスタンスを取得します */
	public static get Testnet(): NetworkCategoryId {
		return new NetworkCategoryId( NetworkCategoryIdValue.Testnet );
	}
	/** @deprecated プライベートネットのネットワークカテゴリIDを表すインスタンスを取得します */
	public static get Privatenet(): NetworkCategoryId {
		return new NetworkCategoryId( NetworkCategoryIdValue.Privatenet );
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
