<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Repository;

use Baywall\Core\Domain\ValueObject\BlockNumber;
use Baywall\Core\Domain\ValueObject\ChainId;

interface CrawledBlockRepository {
	/**
	 * クロール済みブロック番号を取得します
	 *
	 * 未初期化（行が無い）場合は null を返します。初期化は invoice 発行時、更新はクロール時に行われます。
	 *
	 * @return BlockNumber|null
	 */
	public function get( ChainId $chain_id ): ?BlockNumber;

	/** クロール済みブロック番号を保存します */
	public function save( ChainId $chain_id, BlockNumber $crawled_block_number ): void;
}
