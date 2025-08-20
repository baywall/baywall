export class TransactionHash {
	private readonly txHashValue: string;

	private constructor( txHashValue: string ) {
		TransactionHash.checkTransactionHash( txHashValue ); // トランザクションハッシュのフォーマットチェック

		this.txHashValue = txHashValue;
	}
	public static from( txHashValue: string ): TransactionHash {
		return new TransactionHash( txHashValue );
	}

	public get value(): string {
		return this.txHashValue;
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
