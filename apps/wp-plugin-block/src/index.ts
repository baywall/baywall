/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './style.scss';

/**
 * Internal dependencies
 */
import Edit from './edit';
import save from './save';
import metadata from './block.json';
import { WidgetAttributes } from './types/WidgetAttributes';
import { BlockIconProvider } from './lib/icon/BlockIconProvider';

import './i18n/18n'; // i18n初期化処理を実行

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
registerBlockType< WidgetAttributes >( metadata.name, {
	...( metadata as any ),
	icon: new BlockIconProvider().get(),
	attributes: {
		// ※ デフォルト値を設定する場合は、`includes/classes/Types/WidgetAttributesType.php`の設定も確認すること。
		sellingNetworkCategoryId: {
			type: 'number',
			// nullを指定するとシンタックスエラーになるため強制的に型を指定。
			default: null as unknown as number,
		},
		sellingAmount: {
			type: 'string',
			default: null as unknown as string,
		},
		sellingSymbol: {
			type: 'string',
			default: null as unknown as string,
		},
	},
	/**
	 * @see ./edit.js
	 */
	edit: Edit,
	/**
	 * @see ./save.js
	 */
	save,
} );
