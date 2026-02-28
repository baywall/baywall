<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject;

use stdClass;

class CrawledBlockTableRecord extends TableRecordBase {
	public function __construct( stdClass $record ) {
		$record->chain_id     = (int) $record->chain_id;
		$record->block_number = $record->block_number === null ? null : (int) $record->block_number;
		$record->updated_at   = $record->updated_at === null ? null : (string) $record->updated_at;

		$this->import( $record );
	}

	protected int $chain_id;
	protected ?int $block_number;
	protected ?string $updated_at;

	public function chainIdValue(): int {
		return $this->chain_id;
	}
	public function blockNumberValue(): ?int {
		return $this->block_number;
	}
	public function updatedAtValue(): ?string {
		return $this->updated_at;
	}
}
