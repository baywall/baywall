import { type SellingNetworkCategorySelectProps } from './SellingNetworkCategorySelect';
import { useSellingNetworkCategorySelectValue } from './useSellingNetworkCategorySelectValue';
import { useSellingNetworkCategorySelectOnChange } from './useSellingNetworkCategorySelectOnChange';
import { useSellingNetworkCategorySelectDisabled } from './useSellingNetworkCategorySelectDisabled';
import { useSellingNetworkCategorySelectOptions } from './useSellingNetworkCategorySelectOptions';

export const useSellingNetworkCategorySelectProps = (): SellingNetworkCategorySelectProps => {
	return {
		value: useSellingNetworkCategorySelectValue(),
		onChange: useSellingNetworkCategorySelectOnChange(),
		disabled: useSellingNetworkCategorySelectDisabled(),
		options: useSellingNetworkCategorySelectOptions(),
	};
};
