<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\Record;

use Baywall\Core\Infrastructure\WordPress\Database\Record\Base\RecordBase;
use stdClass;

/** 請求書トークンテーブルのレコードを表すクラス */
class InvoiceTokenTableRecord extends RecordBase {

	protected string $invoice_token_hash;
	protected string $invoice_id;
	protected int $expires_at;
	protected ?int $revoked_at;

	public function __construct( stdClass $record ) {
		$record->invoice_id         = (string) $record->invoice_id;
		$record->invoice_token_hash = (string) $record->invoice_token_hash;
		$record->expires_at         = (int) $record->expires_at;
		$record->revoked_at         = $record->revoked_at !== null ? (int) $record->revoked_at : null;

		$this->import( $record );
	}

	public function invoiceIdValue(): string {
		return $this->invoice_id;
	}
	public function invoiceTokenHashValue(): string {
		return $this->invoice_token_hash;
	}
	public function expiresAtValue(): int {
		return $this->expires_at;
	}
	public function revokedAtValue(): ?int {
		return $this->revoked_at;
	}
}
