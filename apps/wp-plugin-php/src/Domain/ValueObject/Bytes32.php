<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

/**
 * bytes32型の値を表すクラス
 */
final class Bytes32 {

	private function __construct( string $bytes32_value ) {
		self::checkValidBytes32Format( $bytes32_value );

		$this->bytes32_value = $bytes32_value;
	}
	private string $bytes32_value;

	public static function from( string $bytes32_value ): self {
		return new self( $bytes32_value );
	}

	/**
	 * 32バイトの値を16進数表記で返します。
	 *
	 * ※ 先頭の`0x`は含まれないことに注意
	 */
	public function value(): string {
		return $this->bytes32_value;
	}

	public function __toString() {
		return $this->bytes32_value;
	}

	public function equals( self $other ): bool {
		return $this->bytes32_value === $other->bytes32_value;
	}

	private static function checkValidBytes32Format( string $bytes32_value ): void {
		if ( ! preg_match( '/^[0-9a-f]{64}$/', $bytes32_value, $matches ) ) {
			throw new \InvalidArgumentException( '[589860EA] Invalid bytes32 format. ' . $bytes32_value );
		}
	}
}
