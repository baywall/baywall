import { useSellingNetworkCategorySelectOptions } from './useSellingNetworkCategorySelectOptions';

/** 販売ネットワークカテゴリの選択肢が無効かどうかを取得します */
export const useSellingNetworkCategorySelectDisabled = (): boolean => {
	const options = useSellingNetworkCategorySelectOptions();
	return options === undefined || options === null || options.length === 0;
};
