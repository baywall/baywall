import { useMemo } from 'react';
import { HttpUrl } from '@baywall/lib-value-object';
import { getGraphQlUrl } from './getGraphQlUrl.js';

/** GraphQLのURLを取得します */
export const useGraphQlUrl = (): HttpUrl | null | undefined => {
	return useMemo(() => getGraphQlUrl(), []);
};
