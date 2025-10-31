import { useCallback, useMemo } from '@wordpress/element';
import { Symbol } from '@serendipity/lib-value-object';
import { type SellingPriceSymbolSelectProps } from './SellingPriceSymbolSelect';
import { useBlockInitDataQuery } from '../../../query/useBlockInitDataQuery';
import { useSelectedSellingPriceSymbolState } from '../hooks/useSelectedSellingPriceSymbolState';
import { useSellableSymbols } from '../hooks/useSellableSymbols';

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

const useOptions = (): SellingPriceSymbolSelectProps[ 'options' ] => {
	const sellableSymbols = useSellableSymbols();

	return useMemo( () => {
		if ( sellableSymbols === undefined ) {
			return undefined;
		} else if ( sellableSymbols === null ) {
			return null;
		}

		return sellableSymbols.map( ( symbol ) => ( {
			label: symbol.value,
			value: symbol.value,
		} ) );
	}, [ sellableSymbols ] );
};
