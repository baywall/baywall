<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Record;

use Baywall\Core\Infrastructure\WordPress\Database\Record\Base\RecordBase;
use stdClass;

class AppContractTableRecord extends RecordBase {

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
