import { untrailingslashit } from '../lib/untrailingslashit';
import { Url } from './base/Url';
import { BlockExplorerBaseUrl } from './BlockExplorerBaseUrl';
import { TransactionHash } from './TransactionHash';

const brand: unique symbol = Symbol( 'BlockExplorerTxUrlBrand' );

/**
 * トランザクションハッシュを指すブロックエクスプローラーURLを表すValue Object
 */
export class BlockExplorerTxUrl extends Url {
	/** 型区別用のフィールド */
	private [ brand ]!: void;

	// eslint-disable-next-line no-useless-constructor
	private constructor( urlValue: string ) {
		super( urlValue );
	}

	public static from( baseUrl: BlockExplorerBaseUrl, txHash: TransactionHash ): BlockExplorerTxUrl {
		return new BlockExplorerTxUrl( untrailingslashit( baseUrl.value ) + '/tx/' + txHash.value );
	}
}
