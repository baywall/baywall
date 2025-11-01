import { useMemo, useState } from 'react';
import { NetworkCategoryId } from '@serendipity/lib-value-object';
import { useBlockEditProps } from '../../provider/block-edit-props/useBlockEditProps';

/**
 * 保存済みの販売ネットワークカテゴリIDを取得します。
 *
 * ※ 現時点ではロードしたタイミングでのネットワークカテゴリIDのみ取得可能です。
 *    投稿を保存後にこの値が変更されることは無いことに注意してください。
 */
export const useSavedSellingNetworkCategoryId = (): NetworkCategoryId | null => {
	const [ networkCategoryId, setNetworkCategoryId ] = useState< NetworkCategoryId | null | undefined >( undefined );
	const {
		attributes: { sellingNetworkCategoryId: sellingNetworkCategoryIdValue },
	} = useBlockEditProps();

	return useMemo( () => {
		if ( networkCategoryId !== undefined ) {
			return networkCategoryId;
		}
		const loadedNetworkCategoryId = sellingNetworkCategoryIdValue
			? NetworkCategoryId.from( sellingNetworkCategoryIdValue )
			: null;
		setNetworkCategoryId( loadedNetworkCategoryId );
		return loadedNetworkCategoryId;
	}, [ networkCategoryId, setNetworkCategoryId, sellingNetworkCategoryIdValue ] );
};
