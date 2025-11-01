import { SelectControl } from '@wordpress/components';
import { SelectControlProps } from '@wordpress/components/build-types/select-control/types';
import { useMemo } from 'react';

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
	return <SelectControl { ...rest } options={ options } />;
};
