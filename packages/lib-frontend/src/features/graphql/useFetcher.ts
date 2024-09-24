import { useGraphQLUrl } from '../../lib/global/useGraphQLUrl';
import { useWpRestNonce } from '../../lib/global/useWpRestNonce';
import { fetcher } from './codegen/_fetcher';
import { createRequestInit } from './_createRequestInit';

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
	const endpoint = useGraphQLUrl();
	const nonce = useWpRestNonce();

	if ( ! endpoint || ! nonce ) {
		throw new Error( `[11D62E9A] endpoint: ${ endpoint }, nonce: ${ nonce }` );
	}

	return {
		endpoint,
		requestInit: createRequestInit( nonce ),
	} as const;
};
