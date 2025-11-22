import { fetcher as _fetcher } from './original/_fetcher';
import { createRequestInit } from './_createRequestInit';
import { getWpRestNonce } from '../php-var/wp-rest-nonce/getWpRestNonce';
import { getGraphQlUrl } from '../graphql-url/getGraphQlUrl';

export const fetcher = < TData, TVariables >( query: string, variables?: TVariables ) => {
	const endpoint = getGraphQlUrl();
	const nonce = getWpRestNonce();

	if ( ! endpoint || ! nonce ) {
		throw new Error( `[EC048815] endpoint: ${ endpoint }, nonce: ${ nonce }` );
	}

	const requestInit = createRequestInit( nonce.value );

	return _fetcher< TData, TVariables >( endpoint.value, requestInit, query.trim(), variables );
};
