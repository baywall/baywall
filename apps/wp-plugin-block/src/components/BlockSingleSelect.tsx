import { SelectControl } from '@wordpress/components';

export interface BlockSingleSelectProps
	extends Extract< React.ComponentProps< typeof SelectControl >, { multiple?: false } > {}

/**
 * ブロックエディタで描画する選択コンポーネント（一つだけ選択可能）
 * @param props
 */
export const BlockSingleSelect: React.FC< BlockSingleSelectProps > = ( props ) => {
	// 通常の`select`コントロールを使用するとテーマにスタイルが影響されるため、
	// WordPressが提供する`SelectControl`コンポーネントを使用
	return <SelectControl { ...props } />;
};
