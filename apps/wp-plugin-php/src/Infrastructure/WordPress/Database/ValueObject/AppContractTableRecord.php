<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject;

use stdClass;

class AppContractTableRecord extends TableRecordBase {
	public function __construct( stdClass $record ) {
		$record->chain_id = (int) $record->chain_id;

		$this->import( $record );
	}

	protected int $chain_id;
	protected string $address;

	public function chainIdValue(): int {
		return $this->chain_id;
	}
	public function addressValue(): string {
		return $this->address;
	}
}
