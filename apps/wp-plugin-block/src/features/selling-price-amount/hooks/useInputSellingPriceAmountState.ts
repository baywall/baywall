import { atom, useAtom } from 'jotai';
import { Amount } from '@serendipity/lib-value-object';

const inputSellingPriceAmountAtom = atom< Amount | null | undefined >( undefined );

/**
 * ユーザーが入力した販売価格
 *
 * ※ 画面で入力した文字が数値として不正な場合はnullが取得されます。
 */
export const useInputSellingPriceAmountState = () => {
	return useAtom( inputSellingPriceAmountAtom );
};
