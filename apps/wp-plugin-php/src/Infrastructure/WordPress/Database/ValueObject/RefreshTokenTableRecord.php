<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Database\ValueObject;

use stdClass;

/** ウォレット認証用のリフレッシュトークンテーブルのレコードを表すクラス */
class RefreshTokenTableRecord extends TableRecordBase {

	protected string $refresh_token_hash;
	protected string $wallet_address;
	protected string $expires_at;
	protected ?string $revoked_at;

	public function __construct( stdClass $record ) {
		$record->refresh_token_hash = (string) $record->refresh_token_hash;
		$record->wallet_address     = (string) $record->wallet_address;
		$record->expires_at         = (string) $record->expires_at;
		$record->revoked_at         = isset( $record->revoked_at ) ? (string) $record->revoked_at : null;

		$this->import( $record );
	}

	public function refreshTokenHashValue(): string {
		return $this->refresh_token_hash;
	}
	public function walletAddressValue(): string {
		return $this->wallet_address;
	}
	public function expiresAtValue(): string {
		return $this->expires_at;
	}
	public function revokedAtValue(): ?string {
		return $this->revoked_at;
	}
}
