<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject;

use stdClass;

class UnlockPaywallTransactionTableRecord extends TableRecordBase {
	public function __construct( stdClass $record ) {
		$record->invoice_id       = (string) $record->invoice_id;
		$record->chain_id         = (int) $record->chain_id;
		$record->block_number     = (int) $record->block_number;
		$record->block_timestamp  = (int) $record->block_timestamp;
		$record->transaction_hash = (string) $record->transaction_hash;

		$this->import( $record );
	}

	protected string $invoice_id;
	protected int $chain_id;
	protected int $block_number;
	protected int $block_timestamp;
	protected string $transaction_hash;

	public function invoiceIdValue(): string {
		return $this->invoice_id;
	}
	public function chainIdValue(): int {
		return $this->chain_id;
	}
	public function blockNumberValue(): int {
		return $this->block_number;
	}
	public function blockTimestampValue(): int {
		return $this->block_timestamp;
	}
	public function transactionHashValue(): string {
		return $this->transaction_hash;
	}
}
