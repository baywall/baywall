import { atom, useAtom } from 'jotai';

// 画面で表示されている金額の文字列
const sellingPriceAmountValueAtom = atom< string | undefined >( undefined );

/** 販売価格の入力された値を取得または設定します */
export const useSellingPriceAmountValueState = () => {
	return useAtom( sellingPriceAmountValueAtom );
};
