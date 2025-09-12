const brand: unique symbol = Symbol( 'PostIdBrand' );

/** 投稿IDを表すvalue-object */
export class PostId {
	/** 型区別用のフィールド */
	private [ brand ]!: void;

	public readonly value: number;

	private constructor( value: number ) {
		PostId.checkPostIdValue( value );
		this.value = value;
	}

	public static from( postIdValue: number ): PostId {
		return new PostId( postIdValue );
	}

	public toString(): string {
		return `${ this.value }`;
	}

	public equals( other: PostId ): boolean {
		return this.value === other.value;
	}

	private static checkPostIdValue( postIdValue: number ): void {
		if ( ! Number.isInteger( postIdValue ) || postIdValue <= 0 ) {
			throw new Error(
				`[260F997A] PostId must be a positive integer. ${ postIdValue } (${ typeof postIdValue })`
			);
		}
	}
}
