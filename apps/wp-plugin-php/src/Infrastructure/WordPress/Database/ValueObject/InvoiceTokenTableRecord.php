<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject;

use stdClass;

/** 請求書トークンテーブルのレコードを表すクラス */
class InvoiceTokenTableRecord extends TableRecordBase {

	protected string $invoice_token_hash;
	protected string $invoice_id;
	protected string $expires_at;
	protected ?string $revoked_at;

	public function __construct( stdClass $record ) {
		$record->invoice_id         = (string) $record->invoice_id;
		$record->invoice_token_hash = (string) $record->invoice_token_hash;
		$record->expires_at         = (string) $record->expires_at;
		$record->revoked_at         = $record->revoked_at !== null ? (string) $record->revoked_at : null;

		$this->import( $record );
	}

	public function invoiceIdValue(): string {
		return $this->invoice_id;
	}
	public function invoiceTokenHashValue(): string {
		return $this->invoice_token_hash;
	}
	public function expiresAtValue(): string {
		return $this->expires_at;
	}
	public function revokedAtValue(): ?string {
		return $this->revoked_at;
	}
}
