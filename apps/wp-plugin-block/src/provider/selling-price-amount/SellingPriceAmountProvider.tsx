import { Amount } from '@serendipity/lib-value-object';
import { createContext, useState } from '@wordpress/element';

type SellingPriceAmountContextType = ReturnType< typeof _useSellingPriceAmount >;

export const SellingPriceAmountContext = createContext< SellingPriceAmountContextType | undefined >( undefined );

const _useSellingPriceAmount = () => {
	const [ sellingPriceAmount, setSellingPriceAmount ] = useState< Amount | null | undefined >( undefined );
	return {
		sellingPriceAmount,
		setSellingPriceAmount,
	};
};

type SellingPriceAmountProviderProps = {
	children: React.ReactNode;
};

/**
 * ユーザーが選択した販売価格の数量を保持するコンテキストプロバイダー
 * @param root0
 * @param root0.children
 */
export const SellingPriceAmountProvider: React.FC< SellingPriceAmountProviderProps > = ( { children } ) => {
	const value = _useSellingPriceAmount();
	return <SellingPriceAmountContext.Provider value={ value }>{ children }</SellingPriceAmountContext.Provider>;
};
