import { type SellingSymbolSelectProps } from './SellingSymbolSelect';
import { useBlockInitDataQuery } from '../../query/useBlockInitDataQuery';
import { useMemo } from '@wordpress/element';

export const useSellingSymbolSelectProps = (): SellingSymbolSelectProps => {
	return {
		disabled: useDisabled(),
		options: useOptions(),
	};
};

const useDisabled = (): boolean => {
	const { data } = useBlockInitDataQuery();
	return data === undefined || data.sellableCurrencies.length === 0;
};

const useOptions = (): NonNullable< SellingSymbolSelectProps[ 'options' ] > => {
	const { data } = useBlockInitDataQuery();

	const sellableSymbolOptions = useMemo( () => {
		return data?.sellableCurrencies.map( ( currency ) => ( {
			label: currency.symbol.value,
			value: currency.symbol.value,
		} ) );
	}, [ data ] );

	// `@wordpress/components`からインポートした`SelectControl`の`options`がundefinedや空配列の場合、
	// コントロール自体が表示されないため、何かしらの選択肢を入れてから返す
	return useMemo( () => {
		if ( sellableSymbolOptions === undefined ) {
			return [ { label: 'Loading...', value: '' } ];
		} else if ( sellableSymbolOptions.length === 0 ) {
			return [ { label: 'No options available', value: '' } ];
		} else {
			return sellableSymbolOptions;
		}
	}, [ sellableSymbolOptions ] );
};
