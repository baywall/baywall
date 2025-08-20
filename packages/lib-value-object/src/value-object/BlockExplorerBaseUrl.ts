import { Url } from './base/Url';

/**
 * ブロックエクスプローラーのベースとなるURLを表すValue Object
 */
export class BlockExplorerBaseUrl extends Url {
	// eslint-disable-next-line no-useless-constructor
	private constructor( baseUrlValue: string ) {
		super( baseUrlValue );
	}

	public static from( baseUrlValue: string ): BlockExplorerBaseUrl {
		return new BlockExplorerBaseUrl( baseUrlValue );
	}
}
