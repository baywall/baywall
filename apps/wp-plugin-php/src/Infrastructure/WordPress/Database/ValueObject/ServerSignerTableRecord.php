<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject;

use stdClass;

class ServerSignerTableRecord extends TableRecordBase {
	/** @disregard P1009 Undefined type */
	public function __construct(
		#[\SensitiveParameter]
		stdClass $record
	) {
		$this->import( $record );
	}

	protected string $address;
	protected string $base64_key;

	public function addressValue(): string {
		return $this->address;
	}
	public function base64KeyValue(): string {
		return $this->base64_key;
	}
}
