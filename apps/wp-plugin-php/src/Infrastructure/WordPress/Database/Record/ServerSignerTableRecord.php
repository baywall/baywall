<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Record;

use Baywall\Core\Infrastructure\WordPress\Database\Record\Base\RecordBase;
use stdClass;

class ServerSignerTableRecord extends RecordBase {
	/** @disregard P1009 Undefined type */
	public function __construct(
		#[\SensitiveParameter]
		stdClass $record
	) {
		$this->import( $record );
	}

	protected string $address;
	protected string $private_key;

	public function addressValue(): string {
		return $this->address;
	}
	public function privateKeyValue(): string {
		return $this->private_key;
	}
}
