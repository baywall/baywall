import { Symbol } from '@serendipity/lib-value-object';
import { createContext, useState } from '@wordpress/element';

type SellingPriceSymbolContextType = ReturnType< typeof _useSellingPriceSymbol >;

export const SellingPriceSymbolContext = createContext< SellingPriceSymbolContextType | undefined >( undefined );

const _useSellingPriceSymbol = () => {
	const [ sellingPriceSymbol, setSellingPriceSymbol ] = useState< Symbol | null | undefined >( undefined );
	return {
		sellingPriceSymbol,
		setSellingPriceSymbol,
	};
};

type SellingPriceSymbolProviderProps = {
	children: React.ReactNode;
};

/**
 * ユーザーが選択した販売価格の通貨シンボルを保持するコンテキストプロバイダー
 * @param root0
 * @param root0.children
 */
export const SellingPriceSymbolProvider: React.FC< SellingPriceSymbolProviderProps > = ( { children } ) => {
	const value = _useSellingPriceSymbol();
	return <SellingPriceSymbolContext.Provider value={ value }>{ children }</SellingPriceSymbolContext.Provider>;
};
