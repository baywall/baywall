/**
 * graphql-condegen(@graphql-codegen/typescript-react-query v6.1.0)が生成したオリジナルのfetcher
 * ※ codegen.tsのconfigをコメントアウトして実行すると、この関数が生成される
 * @param endpoint
 * @param requestInit
 * @param query
 * @param variables
 */
export function fetcher< TData, TVariables >(
	endpoint: string,
	requestInit: RequestInit,
	query: string,
	variables?: TVariables
) {
	return async (): Promise< TData > => {
		const res = await fetch( endpoint, {
			method: 'POST',
			...requestInit,
			body: JSON.stringify( { query, variables } ),
		} );

		const json = await res.json();

		if ( json.errors ) {
			const { message } = json.errors[ 0 ];

			throw new Error( message );
		}

		return json.data;
	};
}
