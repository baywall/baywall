import { type SellingPriceAmountProps } from './SellingPriceAmount';
import { useBlockInitRawDataQuery } from '../../../../query/useBlockInitRawDataQuery';
import { useSellingPriceAmountValueState } from '../../hooks/useSellingPriceAmountValueState';

/** 販売価格（数量部分）の入力値を取得します */
export const useSellingPriceAmountValue = (): NonNullable< SellingPriceAmountProps[ 'value' ] > => {
	const { isLoading } = useBlockInitRawDataQuery();
	const [ value ] = useSellingPriceAmountValueState();

	if ( isLoading ) {
		return ''; // 他のコントロールがデータ取得まで何も表示されないので、それに合わせた制御
	}

	return value ?? '';
};
