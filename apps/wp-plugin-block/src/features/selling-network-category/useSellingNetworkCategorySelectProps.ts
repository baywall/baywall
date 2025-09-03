import { useMemo } from '@wordpress/element';
import { NetworkCategoryId } from '@serendipity/lib-value-object';
import { type SellingNetworkCategorySelectProps } from './SellingNetworkCategorySelect';
import { useBlockInitDataQuery } from '../../query/useBlockInitDataQuery';
import { TextProvider } from '../../lib/i18n/TextProvider';
import { useSelectedSellingNetworkCategoryId } from '../../provider/selected-selling-network-id/useSelectedSellingNetworkCategoryId';

export const useSellingNetworkCategorySelectProps = (): SellingNetworkCategorySelectProps => {
	return {
		value: useValue(),
		onChange: useOnChange(),
		disabled: useDisabled(),
		options: useOptions(),
	};
};

const useValue = (): NonNullable< SellingNetworkCategorySelectProps[ 'value' ] > => {
	const { selectedSellingNetworkCategoryId } = useSelectedSellingNetworkCategoryId();
	return selectedSellingNetworkCategoryId?.value.toString() || '';
};

const useOnChange = (): SellingNetworkCategorySelectProps[ 'onChange' ] => {
	const { setSelectedSellingNetworkCategoryId } = useSelectedSellingNetworkCategoryId();

	return ( value ) => {
		setSelectedSellingNetworkCategoryId( NetworkCategoryId.from( Number( value ) ) );
	};
};

const useDisabled = (): boolean => {
	const { data } = useBlockInitDataQuery();
	return data === undefined || data.sellableCurrencies.length === 0;
};

const useOptions = (): NonNullable< SellingNetworkCategorySelectProps[ 'options' ] > => {
	const { data } = useBlockInitDataQuery();
	const textProvider = useMemo( () => new TextProvider(), [] );

	const sellableNetworkCategoryOptions = useMemo( () => {
		if ( data === undefined ) {
			return undefined;
		}

		const sellableNetworkCategoryIds = [ ...data.sellableNetworkCategoryIds ];
		sellableNetworkCategoryIds.sort( ( a, b ) => a.value - b.value );

		return sellableNetworkCategoryIds.map( ( networkCategoryId ) => ( {
			label: networkCategoryId.value.toString(), // TODO: ネットワークカテゴリ名に変更
			value: networkCategoryId.value.toString(),
		} ) );
	}, [ data ] );

	// `@wordpress/components`からインポートした`SelectControl`の`options`がundefinedや空配列の場合、
	// コントロール自体が表示されないため、何かしらの選択肢を入れてから返す
	return useMemo( () => {
		if ( sellableNetworkCategoryOptions === undefined ) {
			return [ { label: textProvider.loading, value: '' } ];
		} else if ( sellableNetworkCategoryOptions.length === 0 ) {
			return [ { label: textProvider.noOptionsAvailable, value: '' } ];
		} else {
			return sellableNetworkCategoryOptions;
		}
	}, [ sellableNetworkCategoryOptions, textProvider ] );
};
