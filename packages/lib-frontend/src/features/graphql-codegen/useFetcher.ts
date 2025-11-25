import { fetcher } from './fetcher';

/**
 * @param      query
 * @param      variables
 * @deprecated graphql-codegen(@graphql-codegen/typescript-react-query v6.1.0)が生成する
 * useXXMutation関数がReact Hooksに対応できていないためfetcherを使ってください。
 * @see ./fetcher.ts
 */
export const useFetcher = < TData, TVariables >( query: string, variables?: TVariables ) => {
	return fetcher< TData, TVariables >( query, variables );
};
