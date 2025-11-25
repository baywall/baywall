<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Entity;

use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceTokenString;
use Cornix\Serendipity\Core\Domain\ValueObject\UnixTimestamp;

/**
 * 請求書トークンを表すクラス
 */
class InvoiceToken {

	private InvoiceId $invoice_id;
	private InvoiceTokenString $invoice_token;
	private UnixTimestamp $expires_at;
	private ?UnixTimestamp $revoked_at;

	private function __construct( InvoiceId $invoice_id, InvoiceTokenString $invoice_token, UnixTimestamp $expires_at, ?UnixTimestamp $revoked_at ) {
		$this->invoice_id    = $invoice_id;
		$this->invoice_token = $invoice_token;
		$this->expires_at    = $expires_at;
		$this->revoked_at    = $revoked_at;
	}

	public static function create( InvoiceId $invoice_id, InvoiceTokenString $invoice_token, UnixTimestamp $expires_at, ?UnixTimestamp $revoked_at ): self {
		return new self( $invoice_id, $invoice_token, $expires_at, $revoked_at );
	}

	/** 請求書ID */
	public function invoiceId(): InvoiceId {
		return $this->invoice_id;
	}
	/** 請求書トークンの文字列 */
	public function token(): InvoiceTokenString {
		return $this->invoice_token;
	}
	/** 請求書トークンの有効期限 */
	public function expiresAt(): UnixTimestamp {
		return $this->expires_at;
	}
	/** 請求書トークンの取り消し日時 */
	public function revokedAt(): ?UnixTimestamp {
		return $this->revoked_at;
	}

	/** 請求書トークンが期限切れかどうかを取得します */
	public function isExpired(): bool {
		$now = UnixTimestamp::now();
		return $this->expires_at->value() < $now->value();
	}

	/** 請求書トークンが取り消されているかどうかを取得します */
	public function isRevoked(): bool {
		return $this->revoked_at !== null;
	}

	/** 請求書トークンを取り消します */
	public function revoke(): void {
		$this->revoked_at = UnixTimestamp::now();
	}
}
