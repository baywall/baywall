<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Repository;

use Baywall\Core\Domain\Repository\CrawledBlockRepository;
use Baywall\Core\Domain\ValueObject\BlockNumber;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Infrastructure\WordPress\Database\TableGateway\CrawledBlockTable;

class WpCrawledBlockRepository implements CrawledBlockRepository {
	public function __construct(
		private readonly CrawledBlockTable $crawled_block_table
	) {}

	/** @inheritdoc */
	public function get( ChainId $chain_id ): ?BlockNumber {
		return $this->crawled_block_table->get( $chain_id );
	}

	/** @inheritdoc */
	public function save( ChainId $chain_id, BlockNumber $crawled_block_number ): void {
		$this->crawled_block_table->save( $chain_id, $crawled_block_number );
	}
}
