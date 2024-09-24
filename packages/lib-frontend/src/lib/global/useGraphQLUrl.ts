import { usePhpVar } from './_usePhpVar';

/**
 * GraphQLのURLを取得します。
 */
export const useGraphQLUrl = () => {
	const phpVar = usePhpVar();
	return phpVar !== null ? phpVar.graphqlUrl : null;
};
