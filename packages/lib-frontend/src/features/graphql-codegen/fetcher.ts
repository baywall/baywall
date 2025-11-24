import { getWpRestNonce } from '../php-var/wp-rest-nonce/getWpRestNonce';
import { getGraphQlUrl } from '../graphql-url/getGraphQlUrl';
import { GraphqlError } from './error/GraphqlError';

export const fetcher = < TData, TVariables >( query: string, variables?: TVariables ) => {
	const endpoint = getGraphQlUrl();
	const nonce = getWpRestNonce();

	if ( ! endpoint || ! nonce ) {
		throw new Error( `[EC048815] endpoint: ${ endpoint }, nonce: ${ nonce }` );
	}

	return async (): Promise< TData > => {
		const res = await fetch( endpoint.value, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': nonce.value,
			},
			body: JSON.stringify( { query, variables } ),
		} );

		const json = await res.json();

		if ( json.errors ) {
			throw new GraphqlError( json.errors );
		}

		return json.data;
	};
};
