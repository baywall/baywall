<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\ValueObject;

use stdClass;

/** ウォレット認証用のリフレッシュトークンテーブルのレコードを表すクラス */
class RefreshTokenTableRecord extends TableRecordBase {

	protected string $refresh_token_hash;
	protected string $wallet_address;
	protected int $expires_at;
	protected ?int $revoked_at;

	public function __construct( stdClass $record ) {
		$record->refresh_token_hash = (string) $record->refresh_token_hash;
		$record->wallet_address     = (string) $record->wallet_address;
		$record->expires_at         = (int) $record->expires_at;
		$record->revoked_at         = $record->revoked_at !== null ? (int) $record->revoked_at : null;

		$this->import( $record );
	}

	public function refreshTokenHashValue(): string {
		return $this->refresh_token_hash;
	}
	public function walletAddressValue(): string {
		return $this->wallet_address;
	}
	public function expiresAtValue(): int {
		return $this->expires_at;
	}
	public function revokedAtValue(): ?int {
		return $this->revoked_at;
	}
}
