export class NetworkCategoryId {
	public constructor( networkCategoryIdValue: number ) {
		if ( ! isNetworkCategoryIdValue( networkCategoryIdValue ) ) {
			throw new Error( `[E77679FC] Invalid network category ID: ${ networkCategoryIdValue }` );
		}
		this.networkCategoryIdValue = networkCategoryIdValue;
	}

	private readonly networkCategoryIdValue: number;

	public get value(): number {
		return this.networkCategoryIdValue;
	}

	public get isMainnet(): boolean {
		return this.equals( NetworkCategoryId.Mainnet );
	}
	public get isTestnet(): boolean {
		return this.equals( NetworkCategoryId.Testnet );
	}
	public get isPrivatenet(): boolean {
		return this.equals( NetworkCategoryId.Privatenet );
	}

	/** メインネットのネットワークカテゴリIDを表すインスタンスを取得します */
	public static get Mainnet(): NetworkCategoryId {
		return new NetworkCategoryId( NetworkCategoryIdValue.Mainnet );
	}
	/** テストネットのネットワークカテゴリIDを表すインスタンスを取得します */
	public static get Testnet(): NetworkCategoryId {
		return new NetworkCategoryId( NetworkCategoryIdValue.Testnet );
	}
	/** プライベートネットのネットワークカテゴリIDを表すインスタンスを取得します */
	public static get Privatenet(): NetworkCategoryId {
		return new NetworkCategoryId( NetworkCategoryIdValue.Privatenet );
	}

	public equals( other: NetworkCategoryId ): boolean {
		return this.value === other.value;
	}
}

const NetworkCategoryIdValue = {
	Mainnet: 1,
	Testnet: 2,
	Privatenet: 3,
} as const;
type NetworkCategoryIdValue = ( typeof NetworkCategoryIdValue )[ keyof typeof NetworkCategoryIdValue ];

const isNetworkCategoryIdValue = ( networkCategoryIdValue: number ): boolean => {
	return ( Object.values( NetworkCategoryIdValue ) as number[] ).includes( networkCategoryIdValue );
};
