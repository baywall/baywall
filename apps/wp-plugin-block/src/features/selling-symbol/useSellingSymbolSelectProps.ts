import { useCallback, useMemo } from '@wordpress/element';
import { Symbol } from '@serendipity/lib-value-object';
import { type SellingSymbolSelectProps } from './SellingSymbolSelect';
import { useBlockInitDataQuery } from '../../query/useBlockInitDataQuery';
import { TextProvider } from '../../infrastructure/i18n/service/TextProvider';
import { useSelectedSellingSymbol } from '../../provider/selected-selling-symbol/useSelectedSellingSymbol';
import { useSelectedNetworkCategoryId } from '../../provider/widgetState/selectedNetworkCategory/useSelectedNetworkCategoryId';

export const useSellingSymbolSelectProps = (): SellingSymbolSelectProps => {
	return {
		onChange: useOnChange(),
		disabled: useDisabled(),
		options: useOptions(),
	};
};

const useOnChange = (): SellingSymbolSelectProps[ 'onChange' ] => {
	const { setSelectedSellingSymbol } = useSelectedSellingSymbol();

	return useCallback< NonNullable< SellingSymbolSelectProps[ 'onChange' ] > >(
		( value ) => {
			setSelectedSellingSymbol( Symbol.from( value ) );
		},
		[ setSelectedSellingSymbol ]
	);
};

const useDisabled = (): boolean => {
	const { data } = useBlockInitDataQuery();
	return data === undefined || data.sellableCurrencies.length === 0;
};

const useOptions = (): NonNullable< SellingSymbolSelectProps[ 'options' ] > => {
	const { data } = useBlockInitDataQuery();
	const { selectedNetworkCategoryId } = useSelectedNetworkCategoryId();
	const textProvider = useMemo( () => new TextProvider(), [] );

	const sellableSymbolOptions = useMemo( () => {
		if ( data === undefined || selectedNetworkCategoryId === undefined ) {
			return undefined;
		} else if ( selectedNetworkCategoryId === null ) {
			// ネットワークカテゴリが選択されていない場合、販売可能な通貨一覧は空配列とする
			return [];
		}

		return data.sellableCurrencies
			.filter( ( c ) => c.networkCategoryId.equals( selectedNetworkCategoryId ) )
			.map( ( currency ) => ( {
				label: currency.symbol.value,
				value: currency.symbol.value,
			} ) );
	}, [ data, selectedNetworkCategoryId ] );

	// `@wordpress/components`からインポートした`SelectControl`の`options`がundefinedや空配列の場合、
	// コントロール自体が表示されないため、何かしらの選択肢を入れてから返す
	return useMemo( () => {
		if ( sellableSymbolOptions === undefined || selectedNetworkCategoryId === undefined ) {
			return [ { label: textProvider.loading, value: '' } ];
		} else if ( selectedNetworkCategoryId === null ) {
			return [ { label: textProvider.selectSellingNetworkCategory, value: '' } ];
		} else if ( sellableSymbolOptions.length === 0 ) {
			return [ { label: textProvider.noOptionsAvailable, value: '' } ];
		} else {
			return sellableSymbolOptions;
		}
	}, [ sellableSymbolOptions, selectedNetworkCategoryId, textProvider ] );
};
