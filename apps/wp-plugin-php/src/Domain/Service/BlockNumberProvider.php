<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Service;

use Baywall\Core\Domain\ValueObject\BlockNumber;
use Baywall\Core\Domain\ValueObject\BlockTag;
use Baywall\Core\Domain\ValueObject\ChainId;

interface BlockNumberProvider {
	/**
	 * 指定したチェーンIDからブロック番号を取得します。
	 *
	 * @param ChainId       $chain_id チェーンID
	 * @param BlockTag|null $block_tag ブロックタグ（省略時は最新のブロック番号を取得）
	 * @return BlockNumber ブロック番号
	 */
	public function getByChainId( ChainId $chain_id, ?BlockTag $block_tag = null ): BlockNumber;
}
