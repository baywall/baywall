import { useMemo } from '@wordpress/element';
import { type SellingNetworkCategorySelectProps } from './SellingNetworkCategorySelect';
import { useSelectedNetworkCategoryIdState } from '../hooks/useSelectedNetworkCategoryIdState';
import { useSellableNetworkCategories } from '../hooks/useSellableNetworkCategories';

/** 販売ネットワークカテゴリの選択肢を取得します */
export const useSellingNetworkCategorySelectOptions = (): SellingNetworkCategorySelectProps[ 'options' ] => {
	const sellableNetworkCategories = useSellableNetworkCategories();
	const [ selectedNetworkCategoryId ] = useSelectedNetworkCategoryIdState();

	return useMemo( () => {
		if ( sellableNetworkCategories === undefined ) {
			return undefined;
		}

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
	}, [ sellableNetworkCategories, selectedNetworkCategoryId ] );
};
