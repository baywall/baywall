import { getWpRestNonce } from '../php-var/wp-rest-nonce/getWpRestNonce';
import { getGraphQlUrl } from '../graphql-url/getGraphQlUrl';
import { GraphqlError } from './error/GraphqlError';

export const fetcher = < TData, TVariables >( query: string, variables?: TVariables, requestInit?: RequestInit ) => {
	const endpoint = getGraphQlUrl();
	const nonce = getWpRestNonce();

	if ( ! endpoint || ! nonce ) {
		throw new Error( `[EC048815] endpoint: ${ endpoint }, nonce: ${ nonce }` );
	}

	return async (): Promise< TData > => {
		const headers = new Headers( requestInit?.headers );

		headers.set( 'Content-Type', 'application/json' );
		headers.set( 'X-WP-Nonce', nonce.value );

		const res = await fetch( endpoint.value, {
			...requestInit,
			method: 'POST',
			credentials: requestInit?.credentials ?? 'same-origin',
			headers,
			body: JSON.stringify( { query, variables } ),
		} );

		const json = await res.json();

		if ( json.errors ) {
			throw new GraphqlError( json.errors[ 0 ] );
		}

		return json.data;
	};
};
