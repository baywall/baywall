<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Entity;

use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\UnixTimestamp;
use Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject\HashedRefreshToken;

class RefreshTokenInfo {

	private HashedRefreshToken $hashed_refresh_token;
	private Address $wallet_address;
	private UnixTimestamp $expires_at;
	private ?UnixTimestamp $revoked_at;

	private function __construct(
		HashedRefreshToken $hashed_refresh_token,
		Address $wallet_address,
		UnixTimestamp $expires_at,
		?UnixTimestamp $revoked_at
	) {
		$this->hashed_refresh_token = $hashed_refresh_token;
		$this->wallet_address       = $wallet_address;
		$this->expires_at           = $expires_at;
		$this->revoked_at           = $revoked_at;
	}

	public static function create(
		HashedRefreshToken $hashed_refresh_token,
		Address $wallet_address,
		UnixTimestamp $expires_at,
		?UnixTimestamp $revoked_at
	): self {
		return new self( $hashed_refresh_token, $wallet_address, $expires_at, $revoked_at );
	}

	public function hashedRefreshToken(): HashedRefreshToken {
		return $this->hashed_refresh_token;
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
