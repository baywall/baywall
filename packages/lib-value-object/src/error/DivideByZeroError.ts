const brand: unique symbol = Symbol( 'DivideByZeroError' );

/**
 * ゼロ除算エラーを表すクラス
 */
export class DivideByZeroError extends Error {
	// @ts-ignore
	private readonly [ brand ]!: void;

	constructor( message = 'Divide by zero error' ) {
		super( message );
		this.name = 'DivideByZeroError';
	}
}
