import { getWpRestNonce } from '../php-var/wp-rest-nonce/getWpRestNonce.js';
import { getGraphQlUrl } from '../graphql-url/getGraphQlUrl.js';
import { GraphqlError } from './error/GraphqlError.js';
import { HttpError } from '../../error/HttpError.js';

/**
 * GraphQL クエリ文字列を受け取る型。
 *
 * codegen v7 の `typescript-react-query` プラグインは、Document 定数として
 * `TypedDocumentString`(String サブクラス)を生成するため、単なる `string` だけでなく
 * `{ toString(): string }` を満たす任意のオブジェクトも受け入れ可能にする。
 */
export type GraphqlQuery = string | { toString: () => string };

type ResponseBody<TData> = {
	errors?: { message: string; extensions?: { [key: string]: unknown } }[];
	data?: TData;
};

export const fetcher = <TData, TVariables>(query: GraphqlQuery, variables?: TVariables, requestInit?: RequestInit) => {
	const endpoint = getGraphQlUrl();
	const nonce = getWpRestNonce();

	if (!endpoint || !nonce) {
		throw new Error(`[EC048815] endpoint: ${endpoint}, nonce: ${nonce}`);
	}

	return async (): Promise<TData> => {
		const headers = new Headers(requestInit?.headers);

		headers.set('Content-Type', 'application/json');
		headers.set('X-WP-Nonce', nonce.value);

		const res = await fetch(endpoint.value, {
			...requestInit,
			method: 'POST',
			credentials: requestInit?.credentials ?? 'same-origin',
			headers,
			body: JSON.stringify({ query, variables }),
		});

		let json: ResponseBody<TData> | null;
		try {
			json = await res.json();
		} catch (cause) {
			// 非 2xx の本文は HTML 等で JSON でないことがあるため、HTTP status を失わない HttpError にする
			if (!res.ok) {
				throw new HttpError(res);
			}
			// 2xx のパース失敗を HttpError(200) にすると、4xx/5xx を前提とする画面の分類から漏れて通知されなくなる
			throw new Error('[16F32EA3] failed to parse response body', { cause });
		}

		// 本文が JSON の null の場合は res.json() が null を返すため、null 許容で参照する
		const errors = json?.errors;
		// WP 側は GraphQL のエラーを HTTP 200 + errors 配列で返すため、HTTP status より優先する
		if (Array.isArray(errors) && 0 < errors.length) {
			throw new GraphqlError(errors[0]);
		}
		if (!res.ok) {
			throw new HttpError(res);
		}

		return json?.data as TData;
	};
};
