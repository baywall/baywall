import { useEffect, useMemo } from '@wordpress/element';
import { NetworkCategoryId } from '@serendipity/lib-value-object';
import { type SellingNetworkCategorySelectProps } from './SellingNetworkCategorySelect';
import { useBlockInitDataQuery } from '../../query/useBlockInitDataQuery';
import { useSelectedNetworkCategoryIdState } from './hooks/useSelectedNetworkCategoryIdState';
import { useSavedSellingNetworkCategoryId } from '../widget-attributes/useSavedSellingNetworkCategoryId';

export const useSellingNetworkCategorySelectProps = (): SellingNetworkCategorySelectProps => {
	useAutoSelectValue();

	return {
		value: useValue(),
		onChange: useOnChange(),
		disabled: useDisabled(),
		options: useOptions(),
	};
};

/** 販売ネットワークカテゴリを自動的に選択します */
const useAutoSelectValue = (): void => {
	const { data } = useBlockInitDataQuery();
	const [ selectedNetworkCategoryId, setSelectedNetworkCategoryId ] = useSelectedNetworkCategoryIdState();
	const savedNetworkCategoryId = useSavedSellingNetworkCategoryId();

	useEffect( () => {
		if ( selectedNetworkCategoryId !== undefined ) {
			return; // すでに選択されている場合は何もしない
		}
		if ( savedNetworkCategoryId ) {
			// 初期化処理: 保存したネットワークカテゴリIDが存在する場合はロード
			setSelectedNetworkCategoryId( savedNetworkCategoryId );
			return;
		}
		if ( data ) {
			// 初期化処理: 販売可能なネットワークカテゴリが存在する場合は先頭のカテゴリを選択
			setSelectedNetworkCategoryId( data.sellableNetworkCategories[ 0 ]?.id ?? null );
		}
	}, [ data, savedNetworkCategoryId, selectedNetworkCategoryId, setSelectedNetworkCategoryId ] );
};

const useValue = (): NonNullable< SellingNetworkCategorySelectProps[ 'value' ] > => {
	const [ selectedNetworkCategoryId ] = useSelectedNetworkCategoryIdState();
	return selectedNetworkCategoryId?.value.toString() || '';
};

const useOnChange = (): SellingNetworkCategorySelectProps[ 'onChange' ] => {
	const [ , setSelectedNetworkCategoryId ] = useSelectedNetworkCategoryIdState();

	return ( value ) => {
		setSelectedNetworkCategoryId( NetworkCategoryId.from( Number( value ) ) );
	};
};

const useDisabled = (): boolean => {
	const { data } = useBlockInitDataQuery();
	return data === undefined || data.sellableNetworkCategories.length === 0;
};

const useOptions = (): SellingNetworkCategorySelectProps[ 'options' ] => {
	const { data } = useBlockInitDataQuery();
	const [ selectedNetworkCategoryId ] = useSelectedNetworkCategoryIdState();

	return useMemo( () => {
		if ( data === undefined ) {
			return undefined;
		}

		const sellableNetworkCategories = [ ...data.sellableNetworkCategories ];
		sellableNetworkCategories.sort( ( a, b ) => a.id.value - b.id.value );

		const result = sellableNetworkCategories.map( ( networkCategory ) => ( {
			label: networkCategory.name,
			value: networkCategory.id.value.toString(),
			disabled: false,
		} ) );

		// 選択されているネットワークカテゴリIDが現在の販売可能なネットワークカテゴリに存在しない場合は
		// 選択肢に追加して無効化する
		if (
			selectedNetworkCategoryId &&
			! sellableNetworkCategories.find( ( category ) => category.id.equals( selectedNetworkCategoryId ) )
		) {
			result.unshift( {
				label: '',
				value: selectedNetworkCategoryId.value.toString(),
				disabled: true,
			} );
		}
		return result;
	}, [ data, selectedNetworkCategoryId ] );
};
