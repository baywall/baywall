import { Symbol } from '@serendipity/lib-value-object';
import { createContext, useState } from '@wordpress/element';

type SelectedSellingSymbolContextType = ReturnType< typeof _useSelectedSellingSymbol >;

export const SelectedSellingSymbolContext = createContext< SelectedSellingSymbolContextType | undefined >( undefined );

const _useSelectedSellingSymbol = () => {
	const [ selectedSellingSymbol, setSelectedSellingSymbol ] = useState< Symbol | null | undefined >( undefined );
	return {
		selectedSellingSymbol,
		setSelectedSellingSymbol,
	};
};

type SelectedSellingSymbolProviderProps = {
	children: React.ReactNode;
};

/**
 * ユーザーが選択した販売価格の通貨シンボルを保持するコンテキストプロバイダー
 * @param root0
 * @param root0.children
 */
export const SelectedSellingSymbolProvider: React.FC< SelectedSellingSymbolProviderProps > = ( { children } ) => {
	const value = _useSelectedSellingSymbol();
	return <SelectedSellingSymbolContext.Provider value={ value }>{ children }</SelectedSellingSymbolContext.Provider>;
};
