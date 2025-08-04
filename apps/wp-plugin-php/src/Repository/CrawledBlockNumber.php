<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Lib\Option\OptionFactory;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockTag;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;

/**
 * 最後にクロールしたブロック番号を取得または保存するためのクラス
 */
class CrawledBlockNumber {
	/**
	 * 指定したチェーン、ブロックタグで最後にクロールしたブロック番号を取得します。
	 */
	public function get( ChainId $chain_id, BlockTag $block_tag ): ?BlockNumber {
		$block_number_hex = ( new OptionFactory() )->crawledBlockNumberHex( $chain_id, $block_tag )->get();
		return BlockNumber::fromNullable( $block_number_hex );
	}

	/**
	 * 指定したチェーン、ブロックタグで最後にクロールしたブロック番号を保存します。
	 */
	public function set( ChainId $chain_id, BlockTag $block_tag, BlockNumber $block_number ): void {
		( new OptionFactory() )->crawledBlockNumberHex( $chain_id, $block_tag )->update( $block_number->hex() );
	}
}
