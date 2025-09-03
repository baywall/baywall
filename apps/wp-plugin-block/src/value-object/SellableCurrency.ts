import { NetworkCategoryId, Symbol } from '@serendipity/lib-value-object';
import { SellableCurrencyBrand } from './SellableCurrencyBrand';

const brand = SellableCurrencyBrand;

/** 販売可能な通貨を表すvalue-object */
export class SellableCurrency {
	/** 型区別用のフィールド */
	private [ brand ]!: void;

	public readonly networkCategoryId: NetworkCategoryId;
	public readonly symbol: Symbol;

	private constructor( networkCategoryId: NetworkCategoryId, symbol: Symbol ) {
		this.networkCategoryId = networkCategoryId;
		this.symbol = symbol;
	}

	public static from( networkCategoryId: NetworkCategoryId, symbol: Symbol ): SellableCurrency {
		return new SellableCurrency( networkCategoryId, symbol );
	}

	public toString(): string {
		return `${ this.networkCategoryId.toString() }:${ this.symbol.toString() }`;
	}
}
