<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Entity;

use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\UnixTimestamp;
use Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject\RefreshTokenHash;

class RefreshTokenInfo {

	private RefreshTokenHash $refresh_token_hash;
	private Address $wallet_address;
	private UnixTimestamp $expires_at;
	private ?UnixTimestamp $revoked_at;

	private function __construct(
		RefreshTokenHash $refresh_token_hash,
		Address $wallet_address,
		UnixTimestamp $expires_at,
		?UnixTimestamp $revoked_at
	) {
		$this->refresh_token_hash = $refresh_token_hash;
		$this->wallet_address     = $wallet_address;
		$this->expires_at         = $expires_at;
		$this->revoked_at         = $revoked_at;
	}

	public static function create(
		RefreshTokenHash $refresh_token_hash,
		Address $wallet_address,
		UnixTimestamp $expires_at,
		?UnixTimestamp $revoked_at
	): self {
		return new self( $refresh_token_hash, $wallet_address, $expires_at, $revoked_at );
	}

	public function refreshTokenHash(): RefreshTokenHash {
		return $this->refresh_token_hash;
	}
	public function walletAddress(): Address {
		return $this->wallet_address;
	}
	public function expiresAt(): UnixTimestamp {
		return $this->expires_at;
	}
	public function revokedAt(): ?UnixTimestamp {
		return $this->revoked_at;
	}
}
