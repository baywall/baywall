import { useCallback, useMemo } from '@wordpress/element';
import { Symbol } from '@serendipity/lib-value-object';
import { type SellingPriceSymbolSelectProps } from './SellingPriceSymbolSelect';
import { useBlockInitDataQuery } from '../../../query/useBlockInitDataQuery';
import { TextProvider } from '../../../lib/i18n/TextProvider';
import { useSellingPriceSymbol } from '../../../provider/selling-price-symbol/useSellingPriceSymbol';

export const useSellingPriceSymbolSelectProps = (): SellingPriceSymbolSelectProps => {
	return {
		value: useValue(),
		onChange: useOnChange(),
		disabled: useDisabled(),
		options: useOptions(),
	};
};

const useValue = (): NonNullable< SellingPriceSymbolSelectProps[ 'value' ] > => {
	const { sellingPriceSymbol } = useSellingPriceSymbol();
	return sellingPriceSymbol?.value || '';
};

const useOnChange = (): SellingPriceSymbolSelectProps[ 'onChange' ] => {
	const { setSellingPriceSymbol } = useSellingPriceSymbol();

	return useCallback< NonNullable< SellingPriceSymbolSelectProps[ 'onChange' ] > >(
		( value ) => {
			setSellingPriceSymbol( Symbol.from( value ) );
		},
		[ setSellingPriceSymbol ]
	);
};

const useDisabled = (): boolean => {
	const { data } = useBlockInitDataQuery();
	return data === undefined || data.sellableSymbols.length === 0;
};

const useOptions = (): NonNullable< SellingPriceSymbolSelectProps[ 'options' ] > => {
	const { data } = useBlockInitDataQuery();
	const textProvider = useMemo( () => new TextProvider(), [] );

	const sellableSymbolOptions = useMemo( () => {
		if ( data === undefined ) {
			return undefined;
		}

		return data.sellableSymbols.map( ( symbol ) => ( {
			label: symbol.value,
			value: symbol.value,
		} ) );
	}, [ data ] );

	// `@wordpress/components`からインポートした`SelectControl`の`options`がundefinedや空配列の場合、
	// コントロール自体が表示されないため、何かしらの選択肢を入れてから返す
	return useMemo( () => {
		if ( sellableSymbolOptions === undefined ) {
			return [ { label: textProvider.loading, value: '' } ];
		} else if ( sellableSymbolOptions.length === 0 ) {
			return [ { label: textProvider.noOptionsAvailable, value: '' } ];
		} else {
			return sellableSymbolOptions;
		}
	}, [ sellableSymbolOptions, textProvider ] );
};
