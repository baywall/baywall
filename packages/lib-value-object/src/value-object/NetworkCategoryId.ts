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
		return this.networkCategoryIdValue === NetworkCategoryIdValue.Mainnet;
	}
	public get isTestnet(): boolean {
		return this.networkCategoryIdValue === NetworkCategoryIdValue.Testnet;
	}
	public get isPrivatenet(): boolean {
		return this.networkCategoryIdValue === NetworkCategoryIdValue.Privatenet;
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
