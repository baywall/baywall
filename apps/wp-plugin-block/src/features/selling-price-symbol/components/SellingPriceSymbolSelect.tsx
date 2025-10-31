import { useMemo } from 'react';
import { BlockSingleSelect, BlockSingleSelectProps } from '../../../components/BlockSingleSelect';
import { TextProvider } from '../../../lib/i18n/TextProvider';

export interface SellingPriceSymbolSelectProps extends Omit< BlockSingleSelectProps, 'options' > {
	options: { label: string; value: string }[] | null | undefined;
}

/**
 * 販売価格の通貨シンボル選択コンポーネント
 * @param props
 */
export const SellingPriceSymbolSelect: React.FC< SellingPriceSymbolSelectProps > = ( props ) => {
	const { options: propsOptions, ...rest } = props;
	const textProvider = useMemo( () => new TextProvider(), [] );

	// `@wordpress/components`からインポートした`SelectControl`の`options`がundefinedや空配列の場合、
	// コントロール自体が表示されないため、何かしらの選択肢を入れてから返す
	const options = ( () => {
		if ( propsOptions === undefined ) {
			return [ { label: textProvider.loading, value: '' } ];
		} else if ( propsOptions === null ) {
			return [ { label: textProvider.selectSellingNetworkCategory, value: '' } ];
		} else if ( propsOptions.length === 0 ) {
			return [ { label: textProvider.noOptionsAvailable, value: '' } ];
		}
		return propsOptions;
	} )();

	return <BlockSingleSelect options={ options } { ...rest } />;
};
