import { NetworkCategoryId } from '@serendipity/lib-value-object';
import { createContext, useState } from '@wordpress/element';

type SelectedSellingNetworkCategoryIdContextType = ReturnType< typeof _useSelectedSellingNetworkCategoryId >;

export const SelectedSellingNetworkCategoryIdContext = createContext<
	SelectedSellingNetworkCategoryIdContextType | undefined
>( undefined );

const _useSelectedSellingNetworkCategoryId = () => {
	const [ selectedSellingNetworkCategoryId, setSelectedSellingNetworkCategoryId ] = useState<
		NetworkCategoryId | null | undefined
	>( undefined );
	return {
		selectedSellingNetworkCategoryId,
		setSelectedSellingNetworkCategoryId,
	};
};

type SelectedSellingNetworkCategoryIdProviderProps = {
	children: React.ReactNode;
};

/**
 * ユーザーが選択した販売ネットワークカテゴリIDを保持するコンテキストプロバイダー
 * @param root0
 * @param root0.children
 */
export const SelectedSellingNetworkCategoryIdProvider: React.FC< SelectedSellingNetworkCategoryIdProviderProps > = ( {
	children,
} ) => {
	const value = _useSelectedSellingNetworkCategoryId();
	return (
		<SelectedSellingNetworkCategoryIdContext.Provider value={ value }>
			{ children }
		</SelectedSellingNetworkCategoryIdContext.Provider>
	);
};
