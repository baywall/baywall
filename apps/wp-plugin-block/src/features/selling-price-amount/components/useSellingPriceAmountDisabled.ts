import { useBlockInitRawDataQuery } from '../../../shared/api/block-init/useBlockInitRawDataQuery';

/** データ取得中は入力を無効化します */
export const useSellingPriceAmountDisabled = (): boolean => {
	// データ取得中は無効化
	return useBlockInitRawDataQuery().isLoading;
};
