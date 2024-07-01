import { getRestPhpVar } from '../../lib/global/getRestPhpVar';
import { fetcher as _fetcher } from './codegen/_fetcher';
import { createRequestInit } from './_createRequestInit';

export const fetcher = <TData, TVariables>(query: string, variables?: TVariables) => {
	const phpVar = getRestPhpVar();

	const endpoint = phpVar?.graphqlUrl;
	const nonce = phpVar?.wpRestNonce;

	if (!endpoint || !nonce) {
		throw new Error(`[EC048815] endpoint: ${endpoint}, nonce: ${nonce}`);
	}

	const requestInit = createRequestInit(nonce);

	return _fetcher<TData, TVariables>(endpoint, requestInit, query, variables);
};
