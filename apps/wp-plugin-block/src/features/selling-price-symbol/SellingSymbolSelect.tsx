import { BlockSingleSelect, BlockSingleSelectProps } from '../../components/BlockSingleSelect';

export interface SellingSymbolSelectProps extends BlockSingleSelectProps {}

/**
 * 販売価格の通貨シンボル選択コンポーネント
 * @param props
 */
export const SellingSymbolSelect: React.FC< SellingSymbolSelectProps > = ( props ) => {
	return <BlockSingleSelect { ...props } />;
};
