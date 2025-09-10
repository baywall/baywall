import { createRequestInit } from '../../lib/graphql/_createRequestInit';
import { usePhpVar } from '../../hooks/php-var/usePhpVar';
import { fetcher } from '../../lib/graphql/codegen/_fetcher';

/**
 * @param      query
 * @param      variables
 * @deprecated graphql-codegen(@graphql-codegen/typescript-react-query v6.1.0)が生成する
 * useXXMutation関数がReact Hooksに対応できていないためfetcherを使ってください。
 * @see ./fetcher.ts
 */
export const useFetcher = < TData, TVariables >( query: string, variables?: TVariables ) => {
	const { endpoint, requestInit } = useFetchParams();

	return fetcher< TData, TVariables >( endpoint, requestInit, query, variables );
};

const useFetchParams = () => {
	const phpVar = usePhpVar();

	const endpoint = phpVar?.graphqlUrl;
	const nonce = phpVar?.wpRestNonce;

	if ( ! endpoint || ! nonce ) {
		throw new Error( `[11D62E9A] endpoint: ${ endpoint }, nonce: ${ nonce }` );
	}

	return {
		endpoint,
		requestInit: createRequestInit( nonce ),
	} as const;
};
