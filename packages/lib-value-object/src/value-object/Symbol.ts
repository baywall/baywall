import cc from 'currency-codes';

export class Symbol {
	public constructor( symbolValue: string ) {
		checkSymbol( symbolValue );
		this.symbolValue = symbolValue;
	}
	private readonly symbolValue: string;

	/** 通貨シンボルを文字列で取得します */
	public get value(): string {
		return this.symbolValue;
	}

	/**
	 * 通貨記号が法定通貨かどうかを取得します
	 * @param symbolValue
	 */
	public isLegalCurrencySymbol( symbolValue: string ): boolean {
		return cc.code( symbolValue ) !== undefined;
	}
}

const checkSymbol = ( symbolValue: string ): void => {
	if ( ! isSymbol( symbolValue ) ) {
		throw new Error( `[7D19A592] Invalid symbol value: ${ symbolValue }` );
	}
};
const isSymbol = ( symbolValue: string ): boolean => {
	return symbolValue.length > 0;
};
