import { useCallback } from 'react';
import { useSelectedNetworkCategoryId } from '../../provider/widgetState/selectedNetworkCategory/useSelectedNetworkCategoryId';
import { useSelectableNetworkCategories } from './useSelectableNetworkCategories';
import { useGetSellableSymbolsCallback } from '../../provider/serverData/useGetSellableSymbolsCallback';
import { useSelectedPriceSymbol } from '../../provider/widgetState/selectedPriceSymbol/useSelectedPriceSymbol';
import { NetworkCategoryId } from '@serendipity/lib-value-object';
import type { NetworkCategorySelectProps } from './NetworkCategorySelect';

/**
 * ネットワーク選択コンポーネントのプロパティを取得します。
 */
export const useNetworkCategorySelectProps = (): NetworkCategorySelectProps => {
	// 選択されたネットワークはProviderのstateから取得
	const { selectedNetworkCategoryId: value } = useSelectedNetworkCategoryId();

	// 選択可能なネットワークはサーバーから受信した情報から取得される
	const networkCategories = useSelectableNetworkCategories();

	// ネットワークが変更された時のコールバック
	const onChange = useOnChangeCallback();

	// 読み込み中はコントロールを無効化
	const disabled = value === undefined;

	return {
		value,
		networkCategories,
		onChange,
		disabled,
	};
};

/**
 * ネットワークが変更された時のコールバックを取得します。
 */
const useOnChangeCallback = () => {
	const { setSelectedNetworkCategoryId } = useSelectedNetworkCategoryId();

	const { selectedPriceSymbol, setSelectedPriceSymbol } = useSelectedPriceSymbol();
	const getSellableSymbol = useGetSellableSymbolsCallback();

	return useCallback(
		( event: React.ChangeEvent< HTMLSelectElement > ) => {
			const networkCategoryId = new NetworkCategoryId( parseInt( event.target.value ) );
			// 選択されているネットワークを更新
			setSelectedNetworkCategoryId( networkCategoryId );

			// 以下、現在選択されている通貨シンボルが変更後のネットワークに存在しない場合はnullを設定する処理
			// ネットワーク変更後に選択可能な通貨シンボルを取得
			const symbols = getSellableSymbol( networkCategoryId );
			if ( selectedPriceSymbol && symbols && ! symbols.includes( selectedPriceSymbol ) ) {
				// 選択されている通貨シンボルが変更後のネットワークで選択不可の場合はnullに変更
				setSelectedPriceSymbol( null );
			}
		},
		[ setSelectedNetworkCategoryId, selectedPriceSymbol, setSelectedPriceSymbol, getSellableSymbol ]
	);
};
