import { useCallback } from 'react';
import { Symbol } from '@serendipity/lib-value-object';
import { useSelectedSellingPriceSymbolState } from '../hooks/useSelectedSellingPriceSymbolState';
import { type SellingPriceSymbolSelectProps } from './SellingPriceSymbolSelect';

/** 販売価格の通貨シンボルの選択肢が変更されたときのコールバックを取得します */
export const useSellingPriceSymbolSelectOnChange = (): SellingPriceSymbolSelectProps[ 'onChange' ] => {
	const [ , setSellingPriceSymbol ] = useSelectedSellingPriceSymbolState();

	return useCallback< NonNullable< SellingPriceSymbolSelectProps[ 'onChange' ] > >(
		( value ) => {
			setSellingPriceSymbol( Symbol.from( value ) );
		},
		[ setSellingPriceSymbol ]
	);
};
