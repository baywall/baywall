import assert from 'assert';
import { SellingPriceSymbolContext } from './SellingPriceSymbolProvider';
import { useContext } from '@wordpress/element';

/**
 * ユーザーが選択した販売価格の通貨シンボルを取得または設定する機能を提供します。
 */
export const useSellingPriceSymbol = () => {
	const context = useContext( SellingPriceSymbolContext );
	assert( context, '[0482D5F7] Context is not found' );

	return context;
};
