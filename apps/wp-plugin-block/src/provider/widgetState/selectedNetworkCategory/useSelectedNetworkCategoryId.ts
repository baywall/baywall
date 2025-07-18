import assert from 'assert';
import { useContext } from 'react';
import { SelectedNetworkCategoryIdContext } from './SelectedNetworkCategoryIdProvider';

/**
 * ユーザーが選択したネットワークカテゴリを取得または設定する機能を提供します。
 */
export const useSelectedNetworkCategoryId = () => {
	const context = useContext( SelectedNetworkCategoryIdContext );
	assert( context, '[90D2588E] Context is not found' );

	return context;
};
