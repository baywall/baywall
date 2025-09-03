import { BlockNumber, type BlockNumberProps } from '../../components/BlockNumber';

export type SellingPriceAmountProps = BlockNumberProps;

/**
 * 販売価格（数量部分）入力コンポーネント
 * @param props
 */
export const SellingPriceAmount: React.FC< SellingPriceAmountProps > = ( props ) => {
	return <BlockNumber { ...props } />;
};
