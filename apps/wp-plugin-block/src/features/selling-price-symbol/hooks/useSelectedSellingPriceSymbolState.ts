import { atom, useAtom } from 'jotai';
import { Symbol } from '@serendipity/lib-value-object';

const selectedSellingPriceSymbolAtom = atom< Symbol | null | undefined >( undefined );

/** 画面で選択されている販売通貨シンボル */
export const useSelectedSellingPriceSymbolState = () => {
	return useAtom( selectedSellingPriceSymbolAtom );
};
