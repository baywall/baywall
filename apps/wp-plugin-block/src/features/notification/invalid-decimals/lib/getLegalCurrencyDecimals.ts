import cc from 'currency-codes';
import { Decimals, Symbol } from '@serendipity/lib-value-object';

/**
 * 指定した法定通貨の小数点以下桁数を取得します
 *
 * 指定した通貨シンボルが法定通貨でない場合はnullを返します
 * @param symbol
 */
export const getLegalCurrencyDecimals = ( symbol: Symbol ): Decimals | null => {
	const code = cc.code( symbol.value );
	return code ? Decimals.from( code.digits ) : null;
};
