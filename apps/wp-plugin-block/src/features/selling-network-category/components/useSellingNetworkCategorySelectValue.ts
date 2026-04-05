import { type SellingNetworkCategorySelectProps } from './SellingNetworkCategorySelect';
import { useSelectedNetworkCategoryIdState } from '../hooks/useSelectedNetworkCategoryIdState';

/** 販売ネットワークカテゴリの選択済みの値を取得します */
export const useSellingNetworkCategorySelectValue = (): NonNullable< SellingNetworkCategorySelectProps[ 'value' ] > => {
	const [ selectedNetworkCategoryId ] = useSelectedNetworkCategoryIdState();
	return selectedNetworkCategoryId?.value.toString() || '';
};
