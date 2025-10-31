import { useCallback, useMemo } from '@wordpress/element';
import { Symbol } from '@serendipity/lib-value-object';
import { type SellingPriceSymbolSelectProps } from './SellingPriceSymbolSelect';
import { useBlockInitDataQuery } from '../../../query/useBlockInitDataQuery';
import { TextProvider } from '../../../lib/i18n/TextProvider';
import { useSelectedSellingPriceSymbolState } from '../hooks/useSelectedSellingPriceSymbolState';
import { useSelectedNetworkCategoryIdState } from '../../selling-network-category/hooks/useSelectedNetworkCategoryIdState';

export const useSellingPriceSymbolSelectProps = (): SellingPriceSymbolSelectProps => {
	return {
		value: useValue(),
		onChange: useOnChange(),
		disabled: useDisabled(),
		options: useOptions(),
	};
};

const useValue = (): NonNullable< SellingPriceSymbolSelectProps[ 'value' ] > => {
	const [ sellingPriceSymbol ] = useSelectedSellingPriceSymbolState();
	return sellingPriceSymbol?.value || '';
};

const useOnChange = (): SellingPriceSymbolSelectProps[ 'onChange' ] => {
	const [ , setSellingPriceSymbol ] = useSelectedSellingPriceSymbolState();

	return useCallback< NonNullable< SellingPriceSymbolSelectProps[ 'onChange' ] > >(
		( value ) => {
			setSellingPriceSymbol( Symbol.from( value ) );
		},
		[ setSellingPriceSymbol ]
	);
};

const useDisabled = (): boolean => {
	const { data } = useBlockInitDataQuery();
	return data === undefined || data.sellableNetworkCategories.length === 0;
};

const useOptions = (): NonNullable< SellingPriceSymbolSelectProps[ 'options' ] > => {
	const { data } = useBlockInitDataQuery();
	const [ selectedNetworkCategoryId ] = useSelectedNetworkCategoryIdState();
	const textProvider = useMemo( () => new TextProvider(), [] );

	const sellableSymbolOptions = useMemo( () => {
		if ( data === undefined || selectedNetworkCategoryId === undefined ) {
			return undefined;
		} else if ( selectedNetworkCategoryId === null ) {
			return null;
		}

		const sellableSymbols = data.sellableNetworkCategories.find( ( c ) => c.id.equals( selectedNetworkCategoryId ) )
			?.sellableSymbols;
		if ( sellableSymbols === undefined ) {
			// TODO: ※ メインネットで登録後、設定変更によりメインネットで販売可能な通貨シンボルが存在しなくなった場合などにここを通る
			throw new Error( '[A02CEFEE] Selected network category not found in sellableNetworkCategories.' );
		}

		return sellableSymbols.map( ( symbol ) => ( {
			label: symbol.value,
			value: symbol.value,
		} ) );
	}, [ data, selectedNetworkCategoryId ] );

	// `@wordpress/components`からインポートした`SelectControl`の`options`がundefinedや空配列の場合、
	// コントロール自体が表示されないため、何かしらの選択肢を入れてから返す
	return useMemo( () => {
		if ( sellableSymbolOptions === undefined ) {
			return [ { label: textProvider.loading, value: '' } ];
		} else if ( sellableSymbolOptions === null ) {
			return [ { label: textProvider.selectSellingNetworkCategory, value: '' } ];
		} else if ( sellableSymbolOptions.length === 0 ) {
			return [ { label: textProvider.noOptionsAvailable, value: '' } ];
		} else {
			return sellableSymbolOptions;
		}
	}, [ sellableSymbolOptions, textProvider ] );
};
