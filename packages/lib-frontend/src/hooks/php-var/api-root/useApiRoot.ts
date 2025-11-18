import { useMemo } from 'react';
import { HttpUrl } from '@serendipity/lib-value-object';
import { getApiRoot } from '../../../lib/php-var/api-root/getApiRoot';

/** APIのルートURLを取得します */
export const useApiRoot = (): HttpUrl | null | undefined => {
	return useMemo( () => getApiRoot(), [] );
};
