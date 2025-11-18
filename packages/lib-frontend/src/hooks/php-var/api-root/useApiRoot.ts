import { useMemo } from 'react';
import { HttpUrl } from '@serendipity/lib-value-object';
import { getApiRoot } from '../../../lib/php-var/getApiRoot';

/** APIのルートURLを取得します */
export const useApiRoot = (): HttpUrl | null | undefined => {
	return useMemo( () => getApiRoot(), [] );
};
