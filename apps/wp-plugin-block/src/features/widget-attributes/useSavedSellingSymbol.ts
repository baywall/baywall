import { useMemo, useState } from 'react';
import { Symbol } from '@serendipity/lib-value-object';
import { useBlockEditProps } from '../../provider/block-edit-props/useBlockEditProps';

/**
 * 保存済みの販売価格の通貨シンボルを取得します。
 *
 * ※ 現時点ではロードしたタイミングでの通貨シンボルのみ取得可能です。
 *    投稿を保存後にこの値が変更されることは無いことに注意してください。
 */
export const useSavedSellingSymbol = (): Symbol | null => {
	const [ symbol, setSymbol ] = useState< Symbol | null | undefined >( undefined );
	const {
		attributes: { sellingSymbol: sellingSymbolValue },
	} = useBlockEditProps();

	return useMemo( () => {
		if ( symbol !== undefined ) {
			return symbol;
		}
		const loadedSymbol = sellingSymbolValue ? Symbol.from( sellingSymbolValue ) : null;
		setSymbol( loadedSymbol );
		return loadedSymbol;
	}, [ symbol, setSymbol, sellingSymbolValue ] );
};
