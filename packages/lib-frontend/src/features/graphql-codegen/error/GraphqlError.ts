const brand: unique symbol = Symbol( 'GraphqlError' );

export class GraphqlError extends Error {
	/** 型区別用のフィールド */
	// @ts-ignore: unused-variable
	private [ brand ]!: void;

	private graphqlError: GraphqlErrorType;

	public constructor( graphqlError: GraphqlErrorType, cause?: unknown ) {
		super( graphqlError.message, { cause } );
		this.name = 'GraphqlError';
		this.graphqlError = graphqlError;
	}

	public get extensions(): { [ key: string ]: unknown } | undefined {
		return this.graphqlError.extensions;
	}
}

type GraphqlErrorType = {
	message: string;
	extensions?: {
		[ key: string ]: unknown;
	};
};
