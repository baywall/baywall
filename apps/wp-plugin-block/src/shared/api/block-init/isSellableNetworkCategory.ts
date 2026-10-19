import { type BlockInitRawDataType } from './useBlockInitRawDataQuery';

type NetworkCategoryRawData = BlockInitRawDataType['networkCategories'][number];

/**
 * 販売可能なネットワークカテゴリかどうかを判定します
 * @param category
 */
export const isSellableNetworkCategory = (category: NetworkCategoryRawData): boolean => {
	return category.sellableSymbols.length > 0;
};
