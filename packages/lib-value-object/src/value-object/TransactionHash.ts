const brand: unique symbol = Symbol( 'TransactionHashBrand' );

/** トランザクションハッシュを表すvalue-object */
export class TransactionHash {
	/** 型区別用のフィールド */
	private [ brand ]!: void;

	public readonly value: string;

	private constructor( txHashValue: string ) {
		TransactionHash.checkTransactionHash( txHashValue ); // トランザクションハッシュのフォーマットチェック

		this.value = txHashValue;
	}
	public static from( txHashValue: string ): TransactionHash {
		return new TransactionHash( txHashValue );
	}

	public toString(): string {
		return this.value;
	}

	public equals( other: TransactionHash ): boolean {
		return this.value === other.value;
	}

	private static isTransactionHash( txHashValue: string ): boolean {
		// 簡易チェック。トランザクションハッシュは64文字の16進数で、0xのプレフィックス付き。
		return /^0x[a-f0-9]{64}$/.test( txHashValue );
	}

	private static checkTransactionHash( txHashValue: string ): void {
		if ( ! TransactionHash.isTransactionHash( txHashValue ) ) {
			throw new Error( `[95033F1E] Invalid address: ${ txHashValue }` );
		}
	}
}
