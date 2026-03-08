export interface ValueObject< T > {
	equals( other: T ): boolean;

	toString(): string;
}
