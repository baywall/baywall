import { useCallback, useMemo } from '@wordpress/element';
import { Symbol } from '@serendipity/lib-value-object';
import { type SellingSymbolSelectProps } from './SellingSymbolSelect';
import { useBlockInitDataQuery } from '../../query/useBlockInitDataQuery';
import { TextProvider } from '../../lib/i18n/TextProvider';
import { useSellingPriceSymbol } from '../../provider/selling-price-symbol/useSellingPriceSymbol';
import { useSellingNetworkCategoryId } from '../../provider/selling-network-category-id/useSellingNetworkCategoryId';

export const useSellingSymbolSelectProps = (): SellingSymbolSelectProps => {
	return {
		value: useValue(),
		onChange: useOnChange(),
		disabled: useDisabled(),
		options: useOptions(),
	};
};

const useValue = (): NonNullable< SellingSymbolSelectProps[ 'value' ] > => {
	const { sellingPriceSymbol } = useSellingPriceSymbol();
	return sellingPriceSymbol?.value || '';
};

const useOnChange = (): SellingSymbolSelectProps[ 'onChange' ] => {
	const { setSellingPriceSymbol } = useSellingPriceSymbol();

	return useCallback< NonNullable< SellingSymbolSelectProps[ 'onChange' ] > >(
		( value ) => {
			setSellingPriceSymbol( Symbol.from( value ) );
		},
		[ setSellingPriceSymbol ]
	);
};

const useDisabled = (): boolean => {
	const { data } = useBlockInitDataQuery();
	return data === undefined || data.sellableCurrencies.length === 0;
};

const useOptions = (): NonNullable< SellingSymbolSelectProps[ 'options' ] > => {
	const { data } = useBlockInitDataQuery();
	const { sellingNetworkCategoryId } = useSellingNetworkCategoryId();
	const textProvider = useMemo( () => new TextProvider(), [] );

	const sellableSymbolOptions = useMemo( () => {
		if ( data === undefined || sellingNetworkCategoryId === undefined ) {
			return undefined;
		} else if ( sellingNetworkCategoryId === null ) {
			// ネットワークカテゴリが選択されていない場合、販売可能な通貨一覧は空配列とする
			return [];
		}

		return data.sellableCurrencies
			.filter( ( c ) => c.networkCategoryId.equals( sellingNetworkCategoryId ) )
			.map( ( currency ) => ( {
				label: currency.symbol.value,
				value: currency.symbol.value,
			} ) );
	}, [ data, sellingNetworkCategoryId ] );

	// `@wordpress/components`からインポートした`SelectControl`の`options`がundefinedや空配列の場合、
	// コントロール自体が表示されないため、何かしらの選択肢を入れてから返す
	return useMemo( () => {
		if ( sellableSymbolOptions === undefined || sellingNetworkCategoryId === undefined ) {
			return [ { label: textProvider.loading, value: '' } ];
		} else if ( sellingNetworkCategoryId === null ) {
			return [ { label: textProvider.selectSellingNetworkCategory, value: '' } ];
		} else if ( sellableSymbolOptions.length === 0 ) {
			return [ { label: textProvider.noOptionsAvailable, value: '' } ];
		} else {
			return sellableSymbolOptions;
		}
	}, [ sellableSymbolOptions, sellingNetworkCategoryId, textProvider ] );
};
