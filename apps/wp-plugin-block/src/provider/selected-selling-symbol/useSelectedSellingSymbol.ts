import assert from 'assert';
import { SelectedSellingSymbolContext } from './SelectedSellingSymbolProvider';
import { useContext } from '@wordpress/element';

/**
 * ユーザーが選択した販売価格の通貨シンボルを取得または設定する機能を提供します。
 */
export const useSelectedSellingSymbol = () => {
	const context = useContext( SelectedSellingSymbolContext );
	assert( context, '[0482D5F7] Context is not found' );

	return context;
};
