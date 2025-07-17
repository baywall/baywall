import { createContext, useState } from 'react';
import { NetworkCategoryId } from '@serendipity/lib-value-object';

type SelectedNetworkCategoryIdContextType = ReturnType< typeof _useSelectedNetworkCategoryId >;

export const SelectedNetworkCategoryIdContext = createContext< SelectedNetworkCategoryIdContextType | undefined >(
	undefined
);

const _useSelectedNetworkCategoryId = () => {
	const [ selectedNetworkCategoryId, setSelectedNetworkCategoryId ] = useState<
		NetworkCategoryId | null | undefined
	>( undefined );
	return {
		selectedNetworkCategoryId,
		setSelectedNetworkCategoryId,
	};
};

type SelectedNetworkCategoryIdProviderProps = {
	children: React.ReactNode;
};

/**
 * ユーザーが選択したネットワークカテゴリIDを保持するコンテキストプロバイダー
 * @param root0
 * @param root0.children
 */
export const SelectedNetworkCategoryIdProvider: React.FC< SelectedNetworkCategoryIdProviderProps > = ( {
	children,
} ) => {
	const value = _useSelectedNetworkCategoryId();
	return (
		<SelectedNetworkCategoryIdContext.Provider value={ value }>
			{ children }
		</SelectedNetworkCategoryIdContext.Provider>
	);
};
