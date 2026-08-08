import { ClassNameProvider } from './lib/class-name/ClassNameProvider';

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 */
export default function save() {
	const myProps = {
		className: new ClassNameProvider().block,
	};
	//	ウィジェット表示用のクラス名を付与
	const props = window.wp.blockEditor.useBlockProps?.save(myProps) ?? myProps;
	return <aside {...props}></aside>;
}
