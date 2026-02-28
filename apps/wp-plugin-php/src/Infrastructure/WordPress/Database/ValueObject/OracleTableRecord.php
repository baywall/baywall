<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject;

use stdClass;

class OracleTableRecord extends TableRecordBase {
	public function __construct( stdClass $record ) {
		$record->chain_id     = (int) $record->chain_id;
		$record->address      = (string) $record->address;
		$record->base_symbol  = (string) $record->base_symbol;
		$record->quote_symbol = (string) $record->quote_symbol;

		$this->import( $record );
	}

	protected int $chain_id;
	protected string $address;
	protected string $base_symbol;
	protected string $quote_symbol;

	public function chainIdValue(): int {
		return $this->chain_id;
	}
	public function addressValue(): string {
		return $this->address;
	}
	public function baseSymbolValue(): string {
		return $this->base_symbol;
	}
	public function quoteSymbolValue(): string {
		return $this->quote_symbol;
	}
}
