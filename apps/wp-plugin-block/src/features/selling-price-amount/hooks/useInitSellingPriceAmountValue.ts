import { Amount } from '@serendipity/lib-value-object';
import { useEffect } from 'react';
import { useSavedSellingAmount } from '../../widget-attributes/useSavedSellingAmount';
import { useSellingPriceAmountValueState } from './useSellingPriceAmountValueState';

/** 画面で入力されている販売価格を初期化します */
export const useInitSellingPriceAmountValue = () => {
	const savedSellingAmount = useSavedSellingAmount();
	const [ value, setValue ] = useSellingPriceAmountValueState();

	useEffect( () => {
		if ( value !== undefined ) {
			return; // 既に値が設定されている場合は何もしない
		}
		const initAmount = savedSellingAmount ?? Amount.from( '0' );
		setValue( initAmount.value ); // 画面に表示されている値を設定
	}, [ savedSellingAmount, value, setValue ] );
};
