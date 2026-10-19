import { useMemo } from 'react';
import type { SelectControl as SelectControlType } from '@wordpress/components';

const SelectControl = window.wp.components.SelectControl as typeof SelectControlType;
type SelectControlProps = React.ComponentProps<typeof SelectControlType>;

export interface BlockSingleSelectProps extends Omit<Extract<SelectControlProps, { multiple?: false }>, 'options'> {
	options: SelectControlProps['options'] | null | undefined;
}

/**
 * ブロックエディタで描画する選択コンポーネント（一つだけ選択可能）
 * @param props
 */
export const BlockSingleSelect = (props: BlockSingleSelectProps) => {
	const { options: propsOptions, ...rest } = props;

	// `@wordpress/components`からインポートした`SelectControl`の`options`がundefinedや空配列の場合、
	// コントロール自体が表示されないため、空のoptionを設定して描画されるようにする
	const options: BlockSingleSelectProps['options'] = useMemo(() => {
		if (propsOptions === undefined || propsOptions === null || propsOptions.length === 0) {
			return [{ label: '', value: '', disabled: true }];
		}
		return propsOptions;
	}, [propsOptions]);

	// 通常の`select`コントロールを使用するとテーマにスタイルが影響されるため、
	// WordPressが提供する`SelectControl`コンポーネントを使用
	//
	// 40pxデフォルトサイズは@wordpress/components@37.0.0以降で標準化されている（`__next40pxDefaultSize`不要）
	return <SelectControl {...rest} options={options} />;
};
