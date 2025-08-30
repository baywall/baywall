import { untrailingslashit } from '../lib/untrailingslashit';
import { Address } from './Address';
import { Url } from './base/Url';
import { BlockExplorerBaseUrl } from './BlockExplorerBaseUrl';

const brand: unique symbol = Symbol( 'BlockExplorerAddressUrlBrand' );

/**
 * ウォレットアドレスを指すブロックエクスプローラーURLを表すValue Object
 */
export class BlockExplorerAddressUrl extends Url {
	/** 型区別用のフィールド */
	private [ brand ]!: void;

	// eslint-disable-next-line no-useless-constructor
	private constructor( urlValue: string ) {
		super( urlValue );
	}

	public static from( baseUrl: BlockExplorerBaseUrl, address: Address ): BlockExplorerAddressUrl {
		return new BlockExplorerAddressUrl( untrailingslashit( baseUrl.value ) + '/address/' + address.value );
	}
}
