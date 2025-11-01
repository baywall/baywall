import { useInputSellingPriceAmountState } from '../../../selling-price-amount/hooks/useInputSellingPriceAmountState';
import { useSelectedSymbolsMaxDecimals } from './useSelectedSymbolsMaxDecimals';

/** 入力された金額の小数点以下の桁数が不正かどうかを返します */
export const useIsInvalidInputAmountDecimals = () => {
	const [ inputAmount ] = useInputSellingPriceAmountState();
	const maxDecimals = useSelectedSymbolsMaxDecimals();

	if ( ! inputAmount || ! maxDecimals ) {
		return false;
	}

	// 入力された値の小数点以下桁数が最大桁数を超過している場合はtrueを返す
	const amountDecimalsValue = inputAmount.value.split( '.' )[ 1 ]?.length ?? 0;
	return amountDecimalsValue > maxDecimals.value;
};
