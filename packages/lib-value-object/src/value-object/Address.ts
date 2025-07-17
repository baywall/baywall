export class Address {
	public constructor( addressValue: string ) {
		if ( ! isAddress( addressValue ) ) {
			throw new Error( `[F1FB4F7B] Invalid address: ${ addressValue }` );
		}
		this.addressValue = addressValue;
	}
	private readonly addressValue: string;

	public get value(): string {
		return this.addressValue;
	}
}

const isAddress = ( addressValue: string ): boolean => {
	// 簡易チェック。Ethereumのアドレスは40文字の16進数で、0xで始まる。
	// チェックサムの検証は行っていない。
	return /^0x[a-fA-F0-9]{40}$/.test( addressValue );
};
