/**
 * graphql-codegen(@graphql-codegen/typescript-react-query v6.1.0)が生成したオリジナルのfetcher
 * ※ codegen.tsのconfigをコメントアウトして実行すると、この関数が生成される
 * @deprecated この関数はerrorsの詳細が取得できないのでfetcher.tsのfetcher関数を使ってください
 * @param      endpoint
 * @param      requestInit
 * @param      query
 * @param      variables
 */
// istanbul ignore next
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
