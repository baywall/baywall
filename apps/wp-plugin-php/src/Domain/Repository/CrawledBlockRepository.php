<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Repository;

use Baywall\Core\Domain\ValueObject\BlockNumber;
use Baywall\Core\Domain\ValueObject\ChainId;

interface CrawledBlockRepository {
	/**
	 * クロール済みブロック番号を保存します
	 *
	 * 読み取りは AppContractRepository::get()（AppContractView の JOIN）を使うこと。ここに get() を追加しない。
	 */
	public function save( ChainId $chain_id, BlockNumber $crawled_block_number ): void;
}
