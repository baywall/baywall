import { useCallback } from '@wordpress/element';
import { type SellingPriceAmountProps } from './SellingPriceAmount';
import { useSellingPriceAmountValueState } from '../../hooks/useSellingPriceAmountValueState';

/** 販売価格（数量部分）の入力値が変更されたときのコールバックを取得します */
export const useSellingPriceAmountOnChange = (): NonNullable< SellingPriceAmountProps[ 'onChange' ] > => {
	const [ , setValue ] = useSellingPriceAmountValueState();

	return useCallback< NonNullable< SellingPriceAmountProps[ 'onChange' ] > >(
		( e ) => {
			// コントロールに表示されている値（文字列）を更新
			setValue( e.target.value );
		},
		[ setValue ]
	);
};
