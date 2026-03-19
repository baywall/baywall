import { ValueObject } from './base/ValueObject';

const brand: unique symbol = Symbol( 'WpRestNonce' );

/**
 * WordPressのREST API用のNonce
 *
 * X-WP-Nonceヘッダーにセットして使用します
 */
export class WpRestNonce implements ValueObject< WpRestNonce > {
	/** 型区別用のフィールド */
	// @ts-ignore: unused-variable
	private [ brand ]!: void;

	private constructor( public readonly value: string ) {}

	static from( value: string ): WpRestNonce {
		return new WpRestNonce( value );
	}

	public equals( other: WpRestNonce ): boolean {
		return this.value === other.value;
	}

	public toString(): string {
		return this.value;
	}
}
