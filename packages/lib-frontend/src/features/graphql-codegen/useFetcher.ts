import { createRequestInit } from './_createRequestInit';
import { usePhpVar } from '../php-var/usePhpVar';
import { fetcher } from './original/_fetcher';
import { getWpRestNonce } from '../php-var/wp-rest-nonce/getWpRestNonce';

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
	const nonce = getWpRestNonce();

	if ( ! endpoint || ! nonce ) {
		throw new Error( `[11D62E9A] endpoint: ${ endpoint }, nonce: ${ nonce }` );
	}

	return {
		endpoint,
		requestInit: createRequestInit( nonce.value ),
	} as const;
};
