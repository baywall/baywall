import { useMemo } from 'react';
import { HttpUrl } from '@serendipity/lib-value-object';
import { getGraphQlUrl } from './getGraphQlUrl.js';

/** GraphQLのURLを取得します */
export const useGraphQlUrl = (): HttpUrl | null | undefined => {
	return useMemo( () => getGraphQlUrl(), [] );
};
