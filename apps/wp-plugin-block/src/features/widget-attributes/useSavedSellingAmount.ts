import { useMemo, useState } from 'react';
import { Amount } from '@serendipity/lib-value-object';
import { useBlockEditProps } from '../../provider/block-edit-props/useBlockEditProps';

/**
 * 保存済みの販売価格の数量を取得します。
 *
 * ※ 現時点ではロードしたタイミングでの数量のみ取得可能です。
 *    投稿を保存後にこの値が変更されることは無いことに注意してください。
 */
export const useSavedSellingAmount = (): Amount | null => {
	const [ amount, setAmount ] = useState< Amount | null | undefined >( undefined );
	const {
		attributes: { sellingAmount: sellingAmountValue },
	} = useBlockEditProps();

	return useMemo( () => {
		if ( amount !== undefined ) {
			return amount;
		}
		const loadedAmount = sellingAmountValue ? Amount.from( sellingAmountValue ) : null;
		setAmount( loadedAmount );
		return loadedAmount;
	}, [ amount, setAmount, sellingAmountValue ] );
};
