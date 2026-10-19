import { useState } from 'react';
import { Symbol } from '@baywall/lib-value-object';
import { useBlockEditProps } from '../../shared/lib/block-editor/block-edit-props/useBlockEditProps';

/**
 * 保存済みの販売価格の通貨シンボルを取得します。
 *
 * ※ 現時点ではロードしたタイミングでの通貨シンボルのみ取得可能です。
 *    投稿を保存後にこの値が変更されることは無いことに注意してください。
 */
export const useSavedSellingSymbol = (): Symbol | null => {
	const {
		attributes: { sellingSymbol: symbolValue },
	} = useBlockEditProps();

	return useState<Symbol | null>(symbolValue ? Symbol.from(symbolValue) : null)[0];
};
