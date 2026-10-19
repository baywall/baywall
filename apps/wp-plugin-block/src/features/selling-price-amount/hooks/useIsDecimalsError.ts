import { useInputSellingPriceAmount } from './useInputSellingPriceAmount';
import { useMaxAllowedDecimals } from './useMaxAllowedDecimals';
import { isDecimalPlacesError } from '../utils/isDecimalPlacesError';
import { useSelectedNetworkCategoryIdState } from '../../selling-network-category';
import { useSelectedSellingPriceSymbolState } from '../../selling-price-symbol';

/** 入力された金額の小数点以下桁数でエラーが発生しているかどうかを取得します */
export const useIsDecimalsError = (): boolean => {
	const inputAmount = useInputSellingPriceAmount();
	const [selectedNetworkCategoryId] = useSelectedNetworkCategoryIdState();
	const [selectedSellingPriceSymbol] = useSelectedSellingPriceSymbolState();

	const maxDecimals = useMaxAllowedDecimals(selectedNetworkCategoryId, selectedSellingPriceSymbol);

	return isDecimalPlacesError(inputAmount, maxDecimals);
};
