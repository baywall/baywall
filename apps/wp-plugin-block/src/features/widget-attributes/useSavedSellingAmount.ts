import { useState } from 'react';
import { Amount } from '@serendipity/lib-value-object';
import { useBlockEditProps } from '../../provider/block-edit-props/useBlockEditProps';

/**
 * 保存済みの販売価格の数量を取得します。
 *
 * ※ 現時点ではロードしたタイミングでの数量のみ取得可能です。
 *    投稿を保存後にこの値が変更されることは無いことに注意してください。
 */
export const useSavedSellingAmount = (): Amount | null => {
	const {
		attributes: { sellingAmount: amountValue },
	} = useBlockEditProps();

	return useState< Amount | null >( amountValue ? Amount.from( amountValue ) : null )[ 0 ];
};
