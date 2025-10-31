import { NonNegativeNumber, NonNegativeNumberProps } from '../../../components/NonNegativeNumber';

export type SellingPriceAmountProps = NonNegativeNumberProps;

/**
 * 販売価格（数量部分）入力コンポーネント
 * @param props
 */
export const SellingPriceAmount: React.FC< SellingPriceAmountProps > = ( props ) => {
	const MAX_LENGTH = 78 + 1; // 256bit符号なし整数の最大値（78桁） + 小数点（1桁）
	return <NonNegativeNumber { ...props } maxLength={ MAX_LENGTH } />;
};
