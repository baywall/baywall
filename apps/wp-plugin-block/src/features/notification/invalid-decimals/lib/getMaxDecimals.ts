import { Decimals, Symbol } from '@serendipity/lib-value-object';
import { Token } from '../../../../value-object/Token';
import { getLegalCurrencyDecimals } from './getLegalCurrencyDecimals';

/**
 * 指定された通貨シンボルの最大小数点以下桁数を取得します
 * ただし、通貨シンボルが法定通貨の場合は法定通貨の小数点以下桁数を返します
 *
 * @param symbol 通貨シンボル
 * @param dic    小数点以下桁数取得対象となるトークン一覧
 */
export const getMaxDecimals = ( symbol: Symbol, dic: Token[] ): Decimals | null => {
	// 法定通貨の小数点以下桁数を優先して取得する
	const legalCurrencyDecimals = getLegalCurrencyDecimals( symbol );
	if ( legalCurrencyDecimals ) {
		// 法定通貨の小数点以下桁数が存在する場合はそれを返す
		return legalCurrencyDecimals;
	}

	// 同一シンボルのdecimals一覧を取得
	const decimalsList = dic.filter( ( t ) => t.symbol.equals( symbol ) ).map( ( t ) => t.decimals );

	// 最大小数点以下桁数を取得(一覧に同一シンボルが無い場合はnullを返す)
	return decimalsList.length > 0 ? Decimals.from( Math.max( ...decimalsList.map( ( d ) => d.value ) ) ) : null;
};
