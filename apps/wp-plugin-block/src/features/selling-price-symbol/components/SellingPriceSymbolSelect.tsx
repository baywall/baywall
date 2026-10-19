import { BlockSingleSelect, BlockSingleSelectProps } from '../../../shared/ui/BlockSingleSelect';

export interface SellingPriceSymbolSelectProps extends BlockSingleSelectProps {}

/**
 * 販売価格の通貨シンボル選択コンポーネント
 * @param props
 */
export const SellingPriceSymbolSelect = (props: SellingPriceSymbolSelectProps) => {
	const { ...rest } = props;
	return <BlockSingleSelect {...rest} />;
};
