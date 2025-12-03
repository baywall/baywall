import { ValueObject } from './base/ValueObject';

const brand: unique symbol = Symbol( 'HttpUrl' );

/** HTTPのURLを表すvalue-object */
export class HttpUrl implements ValueObject< HttpUrl > {
	/** 型区別用のフィールド */
	private [ brand ]!: void;

	public readonly value: string;

	private constructor( httpUrlValue: string ) {
		HttpUrl.checkUrl( httpUrlValue );
		this.value = httpUrlValue;
	}

	public static from( httpUrlValue: string ): HttpUrl {
		return new HttpUrl( httpUrlValue );
	}

	public equals( other: HttpUrl ): boolean {
		return this.value === other.value;
	}

	public toString(): string {
		return this.value;
	}

	private static checkUrl( httpUrlValue: string ): void {
		if ( ! HttpUrl.isUrl( httpUrlValue ) ) {
			throw new Error( `[2265B3FC] Invalid url value: '${ httpUrlValue }'` );
		}
	}
	private static isUrl( httpUrlValue: string ): boolean {
		try {
			const url = new URL( httpUrlValue );
			return url.protocol === 'http:' || url.protocol === 'https:';
		} catch {
			return false;
		}
	}
}
