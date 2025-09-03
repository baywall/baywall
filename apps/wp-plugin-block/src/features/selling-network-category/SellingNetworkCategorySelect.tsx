import { BlockSingleSelect, BlockSingleSelectProps } from '../../components/BlockSingleSelect';

export interface SellingNetworkCategorySelectProps extends BlockSingleSelectProps {}

/**
 * 販売ネットワークカテゴリ選択コンポーネント
 * @param props
 */
export const SellingNetworkCategorySelect: React.FC< SellingNetworkCategorySelectProps > = ( props ) => {
	return <BlockSingleSelect { ...props } />;
};
