import { NetworkCategoryId } from '@serendipity/lib-value-object';
import { createContext, useState } from '@wordpress/element';

type SellingNetworkCategoryIdContextType = ReturnType< typeof _useSellingNetworkCategoryId >;

export const SellingNetworkCategoryIdContext = createContext< SellingNetworkCategoryIdContextType | undefined >(
	undefined
);

const _useSellingNetworkCategoryId = () => {
	const [ sellingNetworkCategoryId, setSellingNetworkCategoryId ] = useState< NetworkCategoryId | null | undefined >(
		undefined
	);
	return {
		sellingNetworkCategoryId,
		setSellingNetworkCategoryId,
	};
};

type SellingNetworkCategoryIdProviderProps = {
	children: React.ReactNode;
};

/**
 * ユーザーが選択した販売ネットワークカテゴリIDを保持するコンテキストプロバイダー
 * @param root0
 * @param root0.children
 */
export const SellingNetworkCategoryIdProvider: React.FC< SellingNetworkCategoryIdProviderProps > = ( { children } ) => {
	const value = _useSellingNetworkCategoryId();
	return (
		<SellingNetworkCategoryIdContext.Provider value={ value }>
			{ children }
		</SellingNetworkCategoryIdContext.Provider>
	);
};
