import { useSelectedSellingPriceSymbolState } from '../hooks/useSelectedSellingPriceSymbolState';
import { SellingPriceSymbolSelectProps } from './SellingPriceSymbolSelect';

/** 販売価格の通貨シンボルの選択済みの値を取得します */
export const useSellingPriceSymbolSelectValue = (): NonNullable< SellingPriceSymbolSelectProps[ 'value' ] > => {
	const [ sellingPriceSymbol ] = useSelectedSellingPriceSymbolState();
	return sellingPriceSymbol?.value || '';
};
