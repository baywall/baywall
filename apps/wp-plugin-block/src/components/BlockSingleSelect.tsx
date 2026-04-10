import { useMemo } from 'react';
import { SelectControl } from '@wordpress/components';

type SelectControlProps = React.ComponentProps< typeof SelectControl >;

export interface BlockSingleSelectProps extends Omit< Extract< SelectControlProps, { multiple?: false } >, 'options' > {
	options: SelectControlProps[ 'options' ] | null | undefined;
}

/**
 * ブロックエディタで描画する選択コンポーネント（一つだけ選択可能）
 * @param props
 */
export const BlockSingleSelect: React.FC< BlockSingleSelectProps > = ( props ) => {
	const { options: propsOptions, ...rest } = props;

	// `@wordpress/components`からインポートした`SelectControl`の`options`がundefinedや空配列の場合、
	// コントロール自体が表示されないため、空のoptionを設定して描画されるようにする
	const options: BlockSingleSelectProps[ 'options' ] = useMemo( () => {
		if ( propsOptions === undefined || propsOptions === null || propsOptions.length === 0 ) {
			return [ { label: '', value: '', disabled: true } ];
		}
		return propsOptions;
	}, [ propsOptions ] );

	// 通常の`select`コントロールを使用するとテーマにスタイルが影響されるため、
	// WordPressが提供する`SelectControl`コンポーネントを使用
	//
	// `__next40pxDefaultSize`は以下の警告を回避するために設定
	// 36px default size for wp.components.SelectControl is deprecated since version 6.8 and will be removed in version 7.1. Note: Set the `__next40pxDefaultSize` prop to true to start opting into the new default size, which will become the default in a future version.
	return <SelectControl __next40pxDefaultSize { ...rest } options={ options } />;
};
