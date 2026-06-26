import { getWpRestNonce } from '../php-var/wp-rest-nonce/getWpRestNonce.js';
import { getGraphQlUrl } from '../graphql-url/getGraphQlUrl.js';
import { GraphqlError } from './error/GraphqlError.js';

/**
 * GraphQL クエリ文字列を受け取る型。
 *
 * codegen v7 の `typescript-react-query` プラグインは、Document 定数として
 * `TypedDocumentString`(String サブクラス)を生成するため、単なる `string` だけでなく
 * `{ toString(): string }` を満たす任意のオブジェクトも受け入れ可能にする。
 */
export type GraphqlQuery = string | { toString: () => string };

export const fetcher = < TData, TVariables >(
	query: GraphqlQuery,
	variables?: TVariables,
	requestInit?: RequestInit
) => {
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
