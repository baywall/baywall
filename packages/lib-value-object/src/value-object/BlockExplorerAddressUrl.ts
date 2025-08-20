import { untrailingslashit } from '../lib/untrailingslashit';
import { Address } from './Address';
import { Url } from './base/Url';
import { BlockExplorerBaseUrl } from './BlockExplorerBaseUrl';

/**
 * ウォレットアドレスを指すブロックエクスプローラーURLを表すValue Object
 */
export class BlockExplorerAddressUrl extends Url {
	// eslint-disable-next-line no-useless-constructor
	private constructor( urlValue: string ) {
		super( urlValue );
	}

	public static from( baseUrl: BlockExplorerBaseUrl, address: Address ): BlockExplorerAddressUrl {
		return new BlockExplorerAddressUrl( untrailingslashit( baseUrl.value ) + '/address/' + address.value );
	}
}
