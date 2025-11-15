<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Entity;

use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\RefreshTokenString;
use Cornix\Serendipity\Core\Domain\ValueObject\UnixTimestamp;

/**
 * リフレッシュトークンを表すクラス
 */
class RefreshToken {

	private RefreshTokenString $refresh_token_string;
	private Address $wallet_address;
	private UnixTimestamp $expires_at;
	private ?UnixTimestamp $revoked_at;

	private function __construct( RefreshTokenString $refresh_token_string, Address $wallet_address, UnixTimestamp $expires_at, ?UnixTimestamp $revoked_at ) {
		$this->refresh_token_string = $refresh_token_string;
		$this->wallet_address       = $wallet_address;
		$this->expires_at           = $expires_at;
		$this->revoked_at           = $revoked_at;
	}

	public static function create( RefreshTokenString $refresh_token_string, Address $wallet_address, UnixTimestamp $expires_at, ?UnixTimestamp $revoked_at ): self {
		return new self( $refresh_token_string, $wallet_address, $expires_at, $revoked_at );
	}

	/** リフレッシュトークンの文字列 */
	public function token(): RefreshTokenString {
		return $this->refresh_token_string;
	}

	/** トークン所有者のウォレットアドレス */
	public function walletAddress(): Address {
		return $this->wallet_address;
	}

	/** リフレッシュトークンの有効期限 */
	public function expiresAt(): UnixTimestamp {
		return $this->expires_at;
	}

	/** リフレッシュトークンの取り消し日時 */
	public function revokedAt(): ?UnixTimestamp {
		return $this->revoked_at;
	}

	/** リフレッシュトークンが期限切れかどうかを取得します */
	public function isExpired(): bool {
		$now = UnixTimestamp::now();
		return $this->expires_at->value() < $now->value();
	}

	/** リフレッシュトークンが取り消されているかどうかを取得します */
	public function isRevoked(): bool {
		return $this->revoked_at !== null;
	}

	/** リフレッシュトークンを取り消します */
	public function revoke(): void {
		$this->revoked_at = UnixTimestamp::now();
	}
}
