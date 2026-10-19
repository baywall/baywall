import { BlockSingleSelect, BlockSingleSelectProps } from '../../../shared/ui/BlockSingleSelect';

export interface SellingNetworkCategorySelectProps extends BlockSingleSelectProps {}

/**
 * 販売ネットワークカテゴリ選択コンポーネント
 * @param props
 */
export const SellingNetworkCategorySelect = (props: SellingNetworkCategorySelectProps) => {
	return <BlockSingleSelect {...props} />;
};
