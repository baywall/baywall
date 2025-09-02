import { useContext } from '@wordpress/element';
import assert from 'assert';
import { SelectedSellingNetworkCategoryIdContext } from './SelectedSellingNetworkCategoryIdProvider';

/**
 * ユーザーが選択した販売ネットワークカテゴリIDを取得または設定する機能を提供します。
 */
export const useSelectedSellingNetworkCategoryId = () => {
	const context = useContext( SelectedSellingNetworkCategoryIdContext );
	assert( context, '[8E4C58C0] Context is not found' );

	return context;
};
