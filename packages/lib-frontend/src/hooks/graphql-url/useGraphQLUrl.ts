import { useMemo } from 'react';
import { getPhpVar } from '../../lib/php-var/getPhpVar';

/** GraphQLのURLを取得します */
export const useGraphQLUrl = (): string | null | undefined => {
	return useMemo( () => getGraphqlUrl(), [] );
};

/** GraphQLに接続するためのURLを取得します */
const getGraphqlUrl = () => {
	const phpVar = getPhpVar();
	return phpVar === null ? null : phpVar.graphqlUrl;
};
