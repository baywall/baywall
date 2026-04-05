import { type SellingPriceAmountProps } from './SellingPriceAmount';
import { useSellingPriceAmountDisabled } from './useSellingPriceAmountDisabled';
import { useSellingPriceAmountValue } from './useSellingPriceAmountValue';
import { useSellingPriceAmountOnChange } from './useSellingPriceAmountOnChange';

export const useSellingPriceAmountProps = (): SellingPriceAmountProps => {
	return {
		disabled: useSellingPriceAmountDisabled(),
		value: useSellingPriceAmountValue(),
		onChange: useSellingPriceAmountOnChange(),
	};
};
