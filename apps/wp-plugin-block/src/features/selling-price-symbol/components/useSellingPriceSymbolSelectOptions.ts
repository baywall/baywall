import { useMemo } from '@wordpress/element';
import { type SellingPriceSymbolSelectProps } from './SellingPriceSymbolSelect';
import { useSelectedNetworkCategoryIdState } from '../../selling-network-category/hooks/useSelectedNetworkCategoryIdState';
import { useSavedSellingNetworkCategoryId } from '../../widget-attributes/useSavedSellingNetworkCategoryId';
import { useSelectedSellingPriceSymbolState } from '../hooks/useSelectedSellingPriceSymbolState';
import { useSellableSymbols } from '../hooks/useSellableSymbols';

/** 販売価格の通貨シンボルの選択肢を取得します */
export const useSellingPriceSymbolSelectOptions = (): SellingPriceSymbolSelectProps[ 'options' ] => {
	const savedSellingNetworkCategoryId = useSavedSellingNetworkCategoryId();
	const [ selectedNetworkCategoryId ] = useSelectedNetworkCategoryIdState();
	const sellableSymbols = useSellableSymbols( selectedNetworkCategoryId );
	const [ selectedSellingPriceSymbol ] = useSelectedSellingPriceSymbolState();

	return useMemo( () => {
		if ( sellableSymbols === undefined ) {
			return undefined;
		} else if ( sellableSymbols === null ) {
			return null;
		}

		const result = sellableSymbols
			.sort( ( a, b ) => a.value.localeCompare( b.value ) )
			.map( ( symbol ) => ( {
				label: symbol.value,
				value: symbol.value,
				disabled: false,
			} ) );

		// 保存時のネットワークカテゴリIDと同じネットワークカテゴリが画面で選択されているかどうかを取得
		const isLoadedNetworkCategorySelected =
			savedSellingNetworkCategoryId &&
			selectedNetworkCategoryId &&
			savedSellingNetworkCategoryId.equals( selectedNetworkCategoryId );
		// 選択されている通貨シンボルが一覧に存在するかどうかを取得
		const isSelectedSymbolExists =
			selectedSellingPriceSymbol && sellableSymbols.find( ( s ) => s.equals( selectedSellingPriceSymbol ) );

		// 保存時と同じネットワークカテゴリが選択されており、選択済みの通貨シンボルが一覧に存在しない場合は選択肢に追加
		if ( selectedSellingPriceSymbol && isLoadedNetworkCategorySelected && ! isSelectedSymbolExists ) {
			result.unshift( {
				label: selectedSellingPriceSymbol.value,
				value: selectedSellingPriceSymbol.value,
				disabled: true,
			} );
		}

		return result;
	}, [ savedSellingNetworkCategoryId, selectedNetworkCategoryId, sellableSymbols, selectedSellingPriceSymbol ] );
};
