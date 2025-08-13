<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject;

use stdClass;

class TokenTableRecord extends TableRecordBase {
	public function __construct( stdClass $record ) {
		$record->chain_id   = (int) $record->chain_id;
		$record->address    = (string) $record->address;
		$record->symbol     = (string) $record->symbol;
		$record->decimals   = (int) $record->decimals;
		$record->is_payable = (bool) $record->is_payable;

		$this->import( $record );
	}

	protected int $chain_id;
	protected string $address;
	protected string $symbol;
	protected int $decimals;
	protected bool $is_payable;

	public function chainIdValue(): int {
		return $this->chain_id;
	}
	public function addressValue(): string {
		return $this->address;
	}
	public function symbolValue(): string {
		return $this->symbol;
	}
	public function decimalsValue(): int {
		return $this->decimals;
	}
	public function isPayableValue(): bool {
		return $this->is_payable;
	}
}
