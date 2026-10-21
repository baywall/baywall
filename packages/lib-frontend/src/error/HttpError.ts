const brand: unique symbol = Symbol('HttpError');

/**
 * HTTP エラーを表すクラス
 */
export class HttpError extends Error {
	// @ts-ignore
	private readonly [brand]!: void;

	constructor(private response: Response) {
		super(`HTTP Error: ${response.status} ${response.statusText}`);
		this.name = 'HttpError';
	}

	/** HTTP ステータスコード */
	public get status(): number {
		return this.response.status;
	}
}
