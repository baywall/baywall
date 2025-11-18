import { getPhpVar } from '../php-var/getPhpVar';
import { fetcher as _fetcher } from './original/_fetcher';
import { createRequestInit } from './_createRequestInit';
import { getWpRestNonce } from '../php-var/wp-rest-nonce/getWpRestNonce';

export const fetcher = < TData, TVariables >( query: string, variables?: TVariables ) => {
	const phpVar = getPhpVar();

	const endpoint = phpVar?.graphqlUrl;
	const nonce = getWpRestNonce();

	if ( ! endpoint || ! nonce ) {
		throw new Error( `[EC048815] endpoint: ${ endpoint }, nonce: ${ nonce }` );
	}

	const requestInit = createRequestInit( nonce.value );

	return _fetcher< TData, TVariables >( endpoint, requestInit, query, variables );
};
