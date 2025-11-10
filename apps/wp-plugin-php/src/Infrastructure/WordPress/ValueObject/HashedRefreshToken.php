<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject;

/**
 * ハッシュ化されたリフレッシュトークンを表すクラス
 */
class HashedRefreshToken implements \Stringable {

	private string $hashed_token_value;

	private function __construct( string $hashed_token_value ) {
		$this->checkHashedRefreshTokenValue( $hashed_token_value );
		assert( strlen( $hashed_token_value ) === ( 17 + 1 + 64 ), '[7113FB32]' );
		$this->hashed_token_value = $hashed_token_value;
	}

	public static function from( string $hashed_token_value ): self {
		return new self( $hashed_token_value );
	}

	public function value(): string {
		return $this->hashed_token_value;
	}

	public function equals( self $other ): bool {
		return $this->hashed_token_value === $other->hashed_token_value;
	}

	private function checkHashedRefreshTokenValue( string $hashed_token_value ): void {
		if ( ! preg_match( '/^\d{17}\.[0-9a-f]{64}$/', $hashed_token_value ) ) {
			throw new \InvalidArgumentException( '[76857470] Invalid hashed refresh token format: ' . $hashed_token_value );
		}
	}

	public function __toString(): string {
		return $this->hashed_token_value;
	}
}
