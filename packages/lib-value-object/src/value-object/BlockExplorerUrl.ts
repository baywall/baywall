import { Url } from './base/Url';

/**
 * ブロックエクスプローラーのURLを表すValue Object
 */
export class BlockExplorerUrl extends Url {
	// eslint-disable-next-line no-useless-constructor
	private constructor( blockExplorerUrlValue: string ) {
		super( blockExplorerUrlValue );
	}

	public static from( blockExplorerUrlValue: string ): BlockExplorerUrl {
		return new BlockExplorerUrl( blockExplorerUrlValue );
	}
}
