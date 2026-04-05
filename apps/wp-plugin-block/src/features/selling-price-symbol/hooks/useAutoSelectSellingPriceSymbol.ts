import { useEffect } from 'react';
import { Symbol } from '@serendipity/lib-value-object';
import { useSavedSellingSymbol } from '../../widget-attributes/useSavedSellingSymbol';
import { useSellingPriceSymbolSelectOptions } from '../components/useSellingPriceSymbolSelectOptions';
import { useSelectedSellingPriceSymbolState } from './useSelectedSellingPriceSymbolState';

/** 販売価格の通貨シンボルを自動的に選択します */
export const useAutoSelectSellingPriceSymbol = (): void => {
	const savedSellingSymbol = useSavedSellingSymbol();
	const [ selectedSymbol, setSelectedSymbol ] = useSelectedSellingPriceSymbolState();
	const options = useSellingPriceSymbolSelectOptions();

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
		} else if ( ! selectedSymbol ) {
			// 選択されていない場合は先頭の選択肢を設定する
			setSelectedSymbol( Symbol.from( options[ 0 ].value ) );
		}
	}, [ savedSellingSymbol, options, selectedSymbol, setSelectedSymbol ] );
};
