import { useState } from '@wordpress/element';
import { NetworkCategoryId } from '@serendipity/lib-value-object';
import { useBlockEditProps } from '../../provider/block-edit-props/useBlockEditProps';

/**
 * 保存済みの販売ネットワークカテゴリIDを取得します。
 *
 * ※ 現時点ではロードしたタイミングでのネットワークカテゴリIDのみ取得可能です。
 *    投稿を保存後にこの値が変更されることは無いことに注意してください。
 */
export const useSavedSellingNetworkCategoryId = (): NetworkCategoryId | null => {
	const {
		attributes: { sellingNetworkCategoryId: networkCategoryIdValue },
	} = useBlockEditProps();

	return useState< NetworkCategoryId | null >(
		networkCategoryIdValue ? NetworkCategoryId.from( networkCategoryIdValue ) : null
	)[ 0 ];
};
