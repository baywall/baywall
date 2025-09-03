import { useContext } from '@wordpress/element';
import assert from 'assert';
import { SellingPriceAmountContext } from './SellingPriceAmountProvider';

/**
 * ユーザーが選択した販売価格の数量を取得または設定する機能を提供します。
 */
export const useSellingPriceAmount = () => {
	const context = useContext( SellingPriceAmountContext );
	assert( context, '[D2DB770E] Context is not found' );

	return context;
};
