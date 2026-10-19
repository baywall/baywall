import { type SellingPriceSymbolSelectProps } from './SellingPriceSymbolSelect';
import { useSellingPriceSymbolSelectOptions } from './useSellingPriceSymbolSelectOptions';
import { useSellingPriceSymbolSelectValue } from './useSellingPriceSymbolSelectValue';
import { useSellingPriceSymbolSelectDisabled } from './useSellingPriceSymbolSelectDisabled';
import { useSellingPriceSymbolSelectOnChange } from './useSellingPriceSymbolSelectOnChange';

export const useSellingPriceSymbolSelectProps = (): SellingPriceSymbolSelectProps => {
	return {
		value: useSellingPriceSymbolSelectValue(),
		onChange: useSellingPriceSymbolSelectOnChange(),
		disabled: useSellingPriceSymbolSelectDisabled(),
		options: useSellingPriceSymbolSelectOptions(),
	};
};
