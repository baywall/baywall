import { useSellingPriceSymbolSelectOptions } from './useSellingPriceSymbolSelectOptions';

export const useSellingPriceSymbolSelectDisabled = (): boolean => {
	const options = useSellingPriceSymbolSelectOptions();
	return options === undefined || options === null || options.length === 0;
};
