import { createRequestInit } from './_createRequestInit';
import { fetcher } from './original/_fetcher';
import { getWpRestNonce } from '../php-var/wp-rest-nonce/getWpRestNonce';
import { getGraphQlUrl } from '../graphql-url/getGraphQlUrl';

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
	const endpoint = getGraphQlUrl();
	const nonce = getWpRestNonce();

	if ( ! endpoint || ! nonce ) {
		throw new Error( `[11D62E9A] endpoint: ${ endpoint }, nonce: ${ nonce }` );
	}

	return {
		endpoint: endpoint.value,
		requestInit: createRequestInit( nonce.value ),
	} as const;
};
