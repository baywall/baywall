import { useMemo } from '@wordpress/element';
import { type SellingSymbolSelectProps } from './SellingSymbolSelect';
import { useBlockInitDataQuery } from '../../query/useBlockInitDataQuery';
import { TextProvider } from '../../infrastructure/i18n/service/TextProvider';

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
	const textProvider = useMemo( () => new TextProvider(), [] );

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
			return [ { label: textProvider.loading, value: '' } ];
		} else if ( sellableSymbolOptions.length === 0 ) {
			return [ { label: textProvider.noOptionsAvailable, value: '' } ];
		} else {
			return sellableSymbolOptions;
		}
	}, [ sellableSymbolOptions, textProvider ] );
};
