<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject;

/**
 * リフレッシュトークンのハッシュを表すクラス
 */
class RefreshTokenHash implements \Stringable {

	private string $refresh_token_hash_value;

	private function __construct( string $refresh_token_hash_value ) {
		$this->checkRefreshTokenHashValue( $refresh_token_hash_value );
		assert( strlen( $refresh_token_hash_value ) === ( 17 + 1 + 64 ), '[7113FB32]' );
		$this->refresh_token_hash_value = $refresh_token_hash_value;
	}

	public static function from( string $refresh_token_hash_value ): self {
		return new self( $refresh_token_hash_value );
	}

	public function value(): string {
		return $this->refresh_token_hash_value;
	}

	public function equals( self $other ): bool {
		return $this->refresh_token_hash_value === $other->refresh_token_hash_value;
	}

	private function checkRefreshTokenHashValue( string $refresh_token_hash_value ): void {
		if ( ! preg_match( '/^\d{17}\.[0-9a-f]{64}$/', $refresh_token_hash_value ) ) {
			throw new \InvalidArgumentException( '[76857470] Invalid hashed refresh token format: ' . $refresh_token_hash_value );
		}
	}

	public function __toString(): string {
		return $this->refresh_token_hash_value;
	}
}
