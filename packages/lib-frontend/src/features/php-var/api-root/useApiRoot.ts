import { useMemo } from 'react';
import { HttpUrl } from '@baywall/lib-value-object';
import { getApiRoot } from './getApiRoot.js';

/** APIのルートURLを取得します */
export const useApiRoot = (): HttpUrl | null | undefined => {
	return useMemo(() => getApiRoot(), []);
};
