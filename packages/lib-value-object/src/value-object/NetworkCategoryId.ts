import { ValueObject } from './base/ValueObject.js';

const brand: unique symbol = Symbol('NetworkCategoryId');

/** ネットワークカテゴリIDを表すvalue-object */
export class NetworkCategoryId implements ValueObject<NetworkCategoryId> {
	/** 型区別用のフィールド */
	// @ts-ignore: unused-variable
	private [brand]!: void;

	public readonly value: number;

	private constructor(NetworkCategoryIdValue: number) {
		NetworkCategoryId.checkNetworkCategoryIdValue(NetworkCategoryIdValue);
		this.value = NetworkCategoryIdValue;
	}

	public static from(networkCategoryIdValue: number): NetworkCategoryId {
		return new NetworkCategoryId(networkCategoryIdValue);
	}

	public toString(): string {
		return `${this.value}`;
	}

	public equals(other: NetworkCategoryId): boolean {
		return this.value === other.value;
	}

	private static checkNetworkCategoryIdValue(networkCategoryIdValue: number): void {
		if (!NetworkCategoryId.isNetworkCategoryIdValue(networkCategoryIdValue)) {
			throw new Error(`[E77679FC] Invalid network category ID: ${networkCategoryIdValue}`);
		}
	}
	private static isNetworkCategoryIdValue(networkCategoryIdValue: number): boolean {
		return (Object.values(NETWORK_CATEGORY_ID) as number[]).includes(networkCategoryIdValue);
	}
}

export const NETWORK_CATEGORY_ID = {
	MAINNET: 1,
	TESTNET: 2,
	PRIVATENET: 3,
} as const;

export type NetworkCategoryIdValue = (typeof NETWORK_CATEGORY_ID)[keyof typeof NETWORK_CATEGORY_ID];
