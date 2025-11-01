import { useCallback, useEffect, useMemo } from '@wordpress/element';
import { Symbol } from '@serendipity/lib-value-object';
import { type SellingPriceSymbolSelectProps } from './SellingPriceSymbolSelect';
import { useBlockInitDataQuery } from '../../../query/useBlockInitDataQuery';
import { useSelectedSellingPriceSymbolState } from '../hooks/useSelectedSellingPriceSymbolState';
import { useSellableSymbols } from '../hooks/useSellableSymbols';
import { useSavedSellingSymbol } from '../../widget-attributes/useSavedSellingSymbol';
import { useSavedSellingNetworkCategoryId } from '../../widget-attributes/useSavedSellingNetworkCategoryId';
import { useSelectedNetworkCategoryIdState } from '../../selling-network-category/hooks/useSelectedNetworkCategoryIdState';

export const useSellingPriceSymbolSelectProps = (): SellingPriceSymbolSelectProps => {
	useAutoSelectValue();

	return {
		value: useValue(),
		onChange: useOnChange(),
		disabled: useDisabled(),
		options: useOptions(),
	};
};

/** 販売価格の通貨シンボルを自動的に選択します */
const useAutoSelectValue = (): void => {
	const savedSellingSymbol = useSavedSellingSymbol();
	const [ selectedSymbol, setSelectedSymbol ] = useSelectedSellingPriceSymbolState();
	const options = useOptions();

	useEffect( () => {
		if ( selectedSymbol === undefined && !! savedSellingSymbol ) {
			// 初期化処理: 保存した通貨シンボルが存在する場合はロード
			setSelectedSymbol( savedSellingSymbol ); // 本来選択できない通貨シンボルが設定される可能性あり
			return;
		} else if ( options === undefined ) {
			return; // データ取得前は何もしない
		}

		if ( options === null || options.length === 0 ) {
			// 選択可能な選択肢が存在しないにも関わらずnullが設定されていない場合はnullを設定する
			if ( selectedSymbol !== null ) {
				setSelectedSymbol( null );
			}
		} else if ( selectedSymbol && ! options.find( ( o ) => o.value === selectedSymbol.value ) ) {
			// 選択可能な選択肢以外を選択している場合は先頭の選択肢を設定する
			setSelectedSymbol( Symbol.from( options[ 0 ].value ) );
		}
	}, [ savedSellingSymbol, options, selectedSymbol, setSelectedSymbol ] );
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
	const savedSellingNetworkCategoryId = useSavedSellingNetworkCategoryId();
	const [ selectedNetworkCategoryId ] = useSelectedNetworkCategoryIdState();
	const sellableSymbols = useSellableSymbols();
	const [ selectedSellingPriceSymbol ] = useSelectedSellingPriceSymbolState();

	return useMemo( () => {
		if ( sellableSymbols === undefined ) {
			return undefined;
		} else if ( sellableSymbols === null ) {
			return null;
		}

		const result = sellableSymbols.map( ( symbol ) => ( {
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
