import Decimal from 'decimal.js';
import { DivideByZeroError } from '../error/DivideByZeroError';
import { ValueObject } from './base/ValueObject';
import { Decimals } from './Decimals';

const brand: unique symbol = Symbol( 'Amount' );

/** 数量を表すvalue-object */
export class Amount implements ValueObject< Amount > {
	/** 型区別用のフィールド */
	private [ brand ]!: void;

	// 指数表記に変更する桁数
	//
	// uint256 の最大値が78桁なので80桁を指定:
	// https://rareskills.io/post/uint-max-value-solidity
	// type(uint256).max: 115792089237316195423570985008687907853269984665640564039457584007913129639935
	private static readonly TO_EXP_NEG = -81; // 80桁まで指数表記にしない
	private static readonly TO_EXP_POS = 81; // 80桁まで指数表記にしない

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
		const D = Decimal.clone( {
			toExpNeg: Amount.TO_EXP_NEG,
			toExpPos: Amount.TO_EXP_POS,
		} );
		return Amount.from( D.add( this.value, other.value ).toString() );
	}

	public sub( other: Amount ): Amount {
		const D = Decimal.clone( {
			toExpNeg: Amount.TO_EXP_NEG,
			toExpPos: Amount.TO_EXP_POS,
		} );
		return Amount.from( D.sub( this.value, other.value ).toString() );
	}

	public mul( other: Amount ): Amount {
		const D = Decimal.clone( {
			toExpNeg: Amount.TO_EXP_NEG,
			toExpPos: Amount.TO_EXP_POS,
		} );
		return Amount.from( D.mul( this.value, other.value ).toString() );
	}

	public div( other: Amount, decimals: Decimals ): Amount {
		if ( other.isZero() ) {
			throw new DivideByZeroError();
		}
		const D = Decimal.clone( {
			precision: decimals.value,
			toExpNeg: Amount.TO_EXP_NEG,
			toExpPos: Amount.TO_EXP_POS,
		} );
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
