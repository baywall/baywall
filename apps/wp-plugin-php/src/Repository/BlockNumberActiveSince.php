<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Lib\Option\OptionFactory;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;

/**
 * 指定したチェーンが最初に有効になった(≒取引が開始された)ブロック番号を取得するためのクラス
 * ※ このブロック番号からイベントを取得すれば、サイトでの取引全てが取得できる
 *
 * @deprecated Chainのプロパティとして実装する
 */
class BlockNumberActiveSince {
	/**
	 * 指定したチェーンが最初に有効になった(≒取引が開始された)ブロック番号を取得します。
	 */
	public function get( ChainId $chain_id ): ?BlockNumber {
		$block_number_hex = ( new OptionFactory() )->activeSinceBlockNumberHex( $chain_id )->get();
		assert( is_null( $block_number_hex ) || Validate::isHex( $block_number_hex ), "[FF97B758] Invalid block number. - block_number_hex: {$block_number_hex}" );
		return is_null( $block_number_hex ) ? null : BlockNumber::from( $block_number_hex );
	}

	/**
	 * 指定したチェーンが最初に有効になった(≒取引が開始された)ブロック番号を設定します。
	 */
	public function set( ChainId $chain_id, BlockNumber $block_number ): void {
		if ( ! is_null( $this->get( $chain_id ) ) ) {
			// 上書きしない
			throw new \InvalidArgumentException( "[FBE35625] active start block number is already set. chain_id: {$chain_id}" );
		}

		( new OptionFactory() )->activeSinceBlockNumberHex( $chain_id )->update( $block_number->hex() );
	}
}
