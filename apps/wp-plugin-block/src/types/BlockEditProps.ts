/**
 * ブロック編集コンポーネントの props 型定義
 *
 * 出典: @wordpress/blocks の BlockEditProps 型から抽出
 * @see https://github.com/WordPress/gutenberg/blob/trunk/packages/blocks/src/api/types.ts
 */
export interface BlockEditProps<T extends Record<string, unknown>> {
	className: string;
	attributes: T;
	clientId: string;
	isSelected: boolean;
	setAttributes: (attrs: Partial<T> | ((prevAttrs: T) => Partial<T>)) => void;
	context: Record<string, unknown>;
}
