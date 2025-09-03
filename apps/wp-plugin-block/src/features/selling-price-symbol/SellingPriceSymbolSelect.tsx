import { BlockSingleSelect, BlockSingleSelectProps } from '../../components/BlockSingleSelect';

export interface SellingPriceSymbolSelectProps extends BlockSingleSelectProps {}

/**
 * 販売価格の通貨シンボル選択コンポーネント
 * @param props
 */
export const SellingPriceSymbolSelect: React.FC< SellingPriceSymbolSelectProps > = ( props ) => {
	return <BlockSingleSelect { ...props } />;
};
