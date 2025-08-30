import cc from 'currency-codes';
import { SymbolBrand as brand } from './SymbolBrand';

/** 通貨記号を表すvalue-object */
export class Symbol {
	/** 型区別用のフィールド */
	private readonly [ brand ]!: void;

	public readonly value: string;

	private constructor( symbolValue: string ) {
		Symbol.checkSymbol( symbolValue );
		this.value = symbolValue;
	}

	public static from( symbolValue: string ): Symbol {
		return new Symbol( symbolValue );
	}

	public equals( other: Symbol ): boolean {
		return this.value === other.value;
	}

	public toString(): string {
		return this.value;
	}

	/**
	 * 通貨記号が法定通貨かどうかを取得します
	 * @param symbolValue
	 */
	public isLegalCurrencySymbol( symbolValue: string ): boolean {
		return cc.code( symbolValue ) !== undefined;
	}

	private static checkSymbol( symbolValue: string ): void {
		if ( ! Symbol.isSymbol( symbolValue ) ) {
			throw new Error( `[7D19A592] Invalid symbol value: '${ symbolValue }'` );
		}
	}
	private static isSymbol( symbolValue: string ): boolean {
		return symbolValue.length > 0 && symbolValue.trim() === symbolValue;
	}
}
