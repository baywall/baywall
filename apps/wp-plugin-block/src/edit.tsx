/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
// import { __ } from '@wordpress/i18n';

import './editor.scss';

import { BlockEditProps } from './types/BlockEditProps';
import { GutenbergPostEdit } from './GutenbergPostEdit';
import { GutenbergPostEditProvider } from './provider/GutenbergPostEditProvider';
import { WidgetAttributes } from './types/WidgetAttributes';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @param props
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 */
const Edit = (props: BlockEditProps<WidgetAttributes>) => {
	const blockProps = window.wp.blockEditor.useBlockProps?.() ?? {};

	return (
		<div {...blockProps}>
			<GutenbergPostEditProvider blockEditProps={props}>
				<GutenbergPostEdit />
			</GutenbergPostEditProvider>
		</div>
	);
};

export default Edit;
