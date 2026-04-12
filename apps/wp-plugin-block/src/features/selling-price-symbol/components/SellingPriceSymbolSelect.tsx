import { BlockSingleSelect, BlockSingleSelectProps } from '../../../components/BlockSingleSelect';

export interface SellingPriceSymbolSelectProps extends BlockSingleSelectProps {}

/**
 * 販売価格の通貨シンボル選択コンポーネント
 * @param props
 */
export const SellingPriceSymbolSelect = ( props: SellingPriceSymbolSelectProps ) => {
	const { ...rest } = props;
	return <BlockSingleSelect { ...rest } />;
};
