<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Interfaces\ValueObject;

class TransactionHash implements ValueObject {
	private function __construct( string $hash ) {
		$this->hash = $hash;
	}

	private string $hash;

	/**
	 * トランザクションハッシュを取得します。
	 */
	public function value(): string {
		return $this->hash;
	}

	public static function from( string $hash ): self {
		// フォーマットチェック
		if ( ! preg_match( '/^0x[a-f0-9]{64}$/', $hash ) ) {
			throw new \InvalidArgumentException( '[7AF48A4D] Invalid transaction hash format: ' . $hash );
		}

		return new self( $hash );
	}

	public function equals( self $other ): bool {
		return $this->hash === $other->hash;
	}

	/**
	 * トランザクションハッシュを文字列として返します。
	 */
	public function __toString(): string {
		return $this->hash;
	}
}
