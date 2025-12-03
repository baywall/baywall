import { ValueObject } from './base/ValueObject';

const brand: unique symbol = Symbol( 'Decimals' );

/** 小数点以下桁数を表すvalue-object */
export class Decimals implements ValueObject< Decimals > {
	/** 型区別用のフィールド */
	private [ brand ]!: void;

	public readonly value: number;

	private constructor( value: number ) {
		Decimals.checkDecimalsValue( value );
		this.value = value;
	}

	public static from( decimalsValue: number ): Decimals {
		return new Decimals( decimalsValue );
	}

	public toString(): string {
		return `${ this.value }`;
	}

	public equals( other: Decimals ): boolean {
		return this.value === other.value;
	}

	private static checkDecimalsValue( decimalsValue: number ): void {
		if ( ! Number.isInteger( decimalsValue ) || decimalsValue < 0 ) {
			throw new Error(
				`[844AB833] Decimals must be a non negative integer. ${ decimalsValue } (${ typeof decimalsValue })`
			);
		}
	}
}
