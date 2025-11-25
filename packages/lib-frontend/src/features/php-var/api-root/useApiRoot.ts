import { useMemo } from 'react';
import { HttpUrl } from '@serendipity/lib-value-object';
import { getApiRoot } from './getApiRoot';

/** APIのルートURLを取得します */
export const useApiRoot = (): HttpUrl | null | undefined => {
	return useMemo( () => getApiRoot(), [] );
};
