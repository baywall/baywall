export const NETWORK_TYPE = {
	MAINNET: 'mainnet',
	TESTNET: 'testnet',
	PRIVATENET: 'privatenet',
} as const;

export type NetworkType = ( typeof NETWORK_TYPE )[ keyof typeof NETWORK_TYPE ];
