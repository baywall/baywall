import { usePhpVar } from './_usePhpVar';

/**
 * GraphQLのURLを取得します。
 */
export const useGraphQLUrl = () => {
	const restPhpVar = usePhpVar();
	return restPhpVar !== null ? restPhpVar.graphqlUrl : null;
};
