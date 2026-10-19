import { NonNegativeNumber, NonNegativeNumberProps } from '../../../shared/ui/NonNegativeNumber';
import { Config } from '../../../shared/config/Config';

export type SellingPriceAmountProps = NonNegativeNumberProps;

/**
 * 販売価格（数量部分）入力コンポーネント
 * @param props
 */
export const SellingPriceAmount = (props: SellingPriceAmountProps) => {
	return <NonNegativeNumber {...props} maxLength={Config.SELLING_PRICE_AMOUNT_MAX_TEXT_LENGTH} />;
};
