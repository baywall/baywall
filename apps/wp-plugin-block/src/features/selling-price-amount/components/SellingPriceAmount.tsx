import { NonNegativeNumber, NonNegativeNumberProps } from '../../../components/NonNegativeNumber';
import { Config } from '../../../constant/Config';

export type SellingPriceAmountProps = NonNegativeNumberProps;

/**
 * 販売価格（数量部分）入力コンポーネント
 * @param props
 */
export const SellingPriceAmount: React.FC< SellingPriceAmountProps > = ( props ) => {
	return <NonNegativeNumber { ...props } maxLength={ Config.SELLING_PRICE_AMOUNT_MAX_TEXT_LENGTH } />;
};
