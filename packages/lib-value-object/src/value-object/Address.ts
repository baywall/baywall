export class Address {
	public readonly value: string;

	private constructor( addressValue: string ) {
		Address.checkAddress( addressValue ); // アドレスのフォーマットチェック
		this.value = addressValue;
	}

	public static from( addressValue: string ): Address {
		return new Address( addressValue );
	}

	private static isAddress( addressValue: string ): boolean {
		// 簡易チェック。Ethereumのアドレスは40文字の16進数で、0xで始まる。
		// チェックサムの検証は行っていない。
		return /^0x[a-fA-F0-9]{40}$/.test( addressValue );
	}

	private static checkAddress( addressValue: string ): void {
		if ( ! Address.isAddress( addressValue ) ) {
			throw new Error( `[F1FB4F7B] Invalid address: ${ addressValue }` );
		}
	}
}
