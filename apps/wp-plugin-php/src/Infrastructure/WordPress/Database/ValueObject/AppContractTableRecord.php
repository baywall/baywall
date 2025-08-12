<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject;

use stdClass;

class AppContractTableRecord extends TableRecordBase {
	public function __construct( stdClass $record ) {
		$record->chain_id             = (int) $record->chain_id;
		$record->crawled_block_number = $record->crawled_block_number === null ? null : (int) $record->crawled_block_number;

		$this->import( $record );
	}

	protected int $chain_id;
	protected string $address;
	protected ?int $crawled_block_number;
	protected ?string $crawled_block_number_updated_at;

	public function chainIdValue(): int {
		return $this->chain_id;
	}
	public function addressValue(): string {
		return $this->address;
	}
	public function crawledBlockNumberValue(): ?int {
		return $this->crawled_block_number;
	}
	public function crawledBlockNumberUpdatedAtValue(): ?string {
		return $this->crawled_block_number_updated_at;
	}
}
