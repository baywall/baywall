<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Record;

use Baywall\Core\Infrastructure\WordPress\Database\Record\Base\RecordBase;
use stdClass;

class AppContractViewRecord extends RecordBase {

	public function __construct( stdClass $record ) {
		$record->chain_id     = (int) $record->chain_id;
		$record->block_number = $record->block_number === null ? null : (int) $record->block_number;
		$record->updated_at   = $record->updated_at === null ? null : (int) $record->updated_at;

		$this->import( $record );
	}

	public int $chain_id;
	public string $address;
	public ?int $block_number;
	public ?int $updated_at;
}
