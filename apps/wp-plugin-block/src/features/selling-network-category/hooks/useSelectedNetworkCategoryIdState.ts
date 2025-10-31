import { NetworkCategoryId } from '@serendipity/lib-value-object';
import { atom, useAtom } from 'jotai';

const selectedNetworkCategoryIdAtom = atom< NetworkCategoryId | null | undefined >( undefined );

/** 画面で選択されているネットワークカテゴリID */
export const useSelectedNetworkCategoryIdState = () => {
	return useAtom( selectedNetworkCategoryIdAtom );
};
