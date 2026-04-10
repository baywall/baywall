import { useMemo } from '@wordpress/element';
import { Amount } from '@serendipity/lib-value-object';
import { useSellingPriceAmountValueState } from './useSellingPriceAmountValueState';

/** ユーザーが入力した販売価格をAmount型で取得します */
export const useInputSellingPriceAmount = (): Amount | null | undefined => {
	const [ value ] = useSellingPriceAmountValueState();

	return useMemo( () => {
		if ( value === undefined ) {
			return undefined; // ロード中
		}

		try {
			return Amount.from( value ); // 数値として正しい場合
		} catch {
			return null; // 数値として不正な場合はnullとして扱う
		}
	}, [ value ] );
};
