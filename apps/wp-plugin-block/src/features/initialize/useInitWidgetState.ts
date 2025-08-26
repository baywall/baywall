import { useEffect } from 'react';
import { useSelectedNetworkCategoryId } from '../../provider/widgetState/selectedNetworkCategory/useSelectedNetworkCategoryId';
import { useInputPriceValue } from '../../provider/widgetState/inputPriceValue/useInputPriceValue';
import { useSelectedPriceSymbol } from '../../provider/widgetState/selectedPriceSymbol/useSelectedPriceSymbol';
import { useWidgetAttributes } from '../../provider/widgetState/widgetAttributes/useWidgetAttributes';
import { NetworkCategoryId } from '@serendipity/lib-value-object';

/**
 * ウィジェット(ブロック)の状態を初期化します。
 */
export const useInitWidgetState = () => {
	// 選択されているネットワークの初期化
	useInitSelectedNetwork();

	// 入力されている価格の初期化
	useInitPriceValue();

	// 選択されている通貨シンボルの初期化
	useInitSelectedPriceSymbol();
};

/**
 * 画面で選択されているネットワークを初期化します。
 */
const useInitSelectedNetwork = () => {
	// ウィジェットの属性を取得
	const { widgetAttributes } = useWidgetAttributes();

	// ユーザーが選択したネットワーク
	const { selectedNetworkCategoryId, setSelectedNetworkCategoryId } = useSelectedNetworkCategoryId();

	// const selectedNetworkCategoryIdRawValue = selectedNetworkCategoryId === null ? null : selectedNetworkCategoryId?.value;

	useEffect( () => {
		if ( selectedNetworkCategoryId === undefined ) {
			if ( widgetAttributes.sellingNetworkCategoryId === null ) {
				setSelectedNetworkCategoryId( null );
			} else {
				setSelectedNetworkCategoryId( NetworkCategoryId.from( widgetAttributes.sellingNetworkCategoryId ) );
			}
		}
	}, [ widgetAttributes, selectedNetworkCategoryId, setSelectedNetworkCategoryId ] );
};

/**
 * 画面で入力されている価格を初期化します。
 */
const useInitPriceValue = () => {
	// ウィジェットの属性を取得
	const { widgetAttributes } = useWidgetAttributes();

	// ユーザーが入力した価格
	const { inputPriceValue, setInputPriceValue } = useInputPriceValue();

	useEffect( () => {
		if ( inputPriceValue === undefined ) {
			const { sellingAmount } = widgetAttributes;
			setInputPriceValue( sellingAmount );
		}
	}, [ widgetAttributes, inputPriceValue, setInputPriceValue ] );
};

/**
 * 画面で選択されている通貨シンボルを初期化します。
 */
const useInitSelectedPriceSymbol = () => {
	// ウィジェットの属性を取得
	const { widgetAttributes } = useWidgetAttributes();

	// ユーザーが選択した通貨シンボル
	const { selectedPriceSymbol, setSelectedPriceSymbol } = useSelectedPriceSymbol();

	useEffect( () => {
		if ( selectedPriceSymbol === undefined ) {
			setSelectedPriceSymbol( widgetAttributes.sellingSymbol );
		}
	}, [ widgetAttributes, selectedPriceSymbol, setSelectedPriceSymbol ] );
};
