import Decimal from 'decimal.js';
import { DivideByZeroError } from '../error/DivideByZeroError';
import { ValueObject } from './base/ValueObject';
import { Decimals } from './Decimals';

const brand: unique symbol = Symbol( 'Amount' );

/** 数量を表すvalue-object */
export class Amount implements ValueObject< Amount > {
	/** 型区別用のフィールド */
	private [ brand ]!: void;

	// 値を10進数の文字列で保持
	public readonly value: string;

	private constructor( value: string ) {
		Amount.checkAmountValue( value );
		this.value = Amount.format( value );
	}

	public static from( amountValue: string ): Amount {
		return new Amount( amountValue );
	}

	public add( other: Amount ): Amount {
		return Amount.from( Decimal.add( this.value, other.value ).toString() );
	}

	public sub( other: Amount ): Amount {
		return Amount.from( Decimal.sub( this.value, other.value ).toString() );
	}

	public mul( other: Amount ): Amount {
		return Amount.from( Decimal.mul( this.value, other.value ).toString() );
	}

	public div( other: Amount, decimals: Decimals ): Amount {
		if ( other.isZero() ) {
			throw new DivideByZeroError();
		}
		const D = Decimal.clone( { precision: decimals.value } );
		return Amount.from( D.div( this.value, other.value ).toString() );
	}

	public equals( other: Amount ): boolean {
		return this.value === other.value;
	}

	public toString(): string {
		return this.value;
	}

	public isZero(): boolean {
		return this.value === '0';
	}

	public isNegative(): boolean {
		return this.value.startsWith( '-' );
	}

	private static format( amountValue: string ): string {
		// 小数点がある場合
		if ( amountValue.includes( '.' ) ) {
			amountValue = amountValue
				.replace( /0+$/, '' ) // 末尾の0を削除
				.replace( /\.$/, '' ); // 末尾が小数点の場合、小数点を削除
		}
		return amountValue;
	}

	private static checkAmountValue( amountValue: string ): void {
		if ( ! Amount.isAmountValue( amountValue ) ) {
			throw new Error( `[9664AE79] Invalid amount value: '${ amountValue }'` );
		}
	}

	private static isAmountValue( amountValue: string ): boolean {
		// 整数または小数点を含む数字の文字列であるかどうかをチェック
		// 例: "123", "-123", "123.45", "-123.45"
		return /^-?(?:0|[1-9]\d*)(\.\d+)?$/.test( amountValue );
	}
}
