import assert from 'assert';
import { BlockEditPropsContext } from './BlockEditPropsProvider';
import { useContext } from '@wordpress/element';

/**
 * ブロックのプロパティを取得または設定する機能を提供します。
 */
export const useBlockEditProps = () => {
	const context = useContext( BlockEditPropsContext );
	assert( context, '[CA202D97] Context is not found' );

	return context;
};
