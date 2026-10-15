<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\ValueObject;

use Baywall\Core\Constant\Config;
use Baywall\Core\Domain\ValueObject\Interfaces\ValueObject;
use kornrunner\Keccak;

/**
 * アドレス(ウォレットアドレス/コントラクトアドレス)を表すクラス
 */
final class Address implements ValueObject {

	private function __construct( string $address_value ) {
		self::checkValidAddressFormat( $address_value );
		// アドレスは常に小文字で保持する
		$this->address_value = strtolower( $address_value );
	}
	private readonly string $address_value;

	public static function from( string $address_value ): self {
		return new self( $address_value );
	}

	public static function fromNullable( ?string $address_value ): ?self {
		return is_null( $address_value ) ? null : new self( $address_value );
	}

	public function value(): string {
		return $this->address_value;
	}

	/** アドレスの値をバイナリ形式で取得します */
	public function bin(): string {
		$bin = hex2bin( str_replace( '0x', '', $this->address_value ) );
		assert( strlen( $bin ) === 20 );
		return $bin;
	}

	public function toBytes32Hex(): string {
		return '0x' . str_pad( substr( $this->address_value, 2 ), 64, '0', STR_PAD_LEFT );
	}

	public function __toString(): string {
		return $this->address_value;
	}

	public function equals( self $other ): bool {
		return $this->address_value === $other->address_value;
	}

	private static function checkValidAddressFormat( string $address_value ): void {
		// 本アプリでは`0x`プレフィックスを必須とする
		$is_valid = str_starts_with( $address_value, '0x' );
		if ( $is_valid ) {
			$hex40 = substr( $address_value, 2 );
			// 全小文字は無条件で、それ以外はEIP-55チェックサムに厳密一致する場合のみ有効
			$is_valid = preg_match( '/^[0-9a-f]{40}$/', $hex40 ) === 1 || self::isValidEip55Checksum( $hex40 );
		}
		if ( ! $is_valid ) {
			throw new \InvalidArgumentException( '[B4AE59FC] Invalid address format. ' . $address_value );
		}
	}

	/**
	 * EIP-55チェックサムに厳密一致するかを判定します。
	 *
	 * @param string $hex40 `0x`を除いた40文字HEX
	 * @see https://eips.ethereum.org/EIPS/eip-55
	 */
	private static function isValidEip55Checksum( string $hex40 ): bool {
		// PCREの`$`は末尾改行の直前にもマッチするため、長さガードで40文字ちょうどを保証する
		if ( 40 !== strlen( $hex40 ) || preg_match( '/^[0-9a-fA-F]{40}$/', $hex40 ) !== 1 ) {
			return false;
		}
		$hash = Keccak::hash( strtolower( $hex40 ), 256 );
		for ( $i = 0; $i < 40; $i++ ) {
			if (
				( intval( $hash[ $i ], 16 ) > 7 && strtoupper( $hex40[ $i ] ) !== $hex40[ $i ] ) ||
				( intval( $hash[ $i ], 16 ) <= 7 && strtolower( $hex40[ $i ] ) !== $hex40[ $i ] )
			) {
				return false;
			}
		}
		return true;
	}

	/** ゼロアドレスを取得します */
	public static function zero(): self {
		return self::from( '0x0000000000000000000000000000000000000000' );
	}

	/** ネイティブトークンのアドレスを取得します */
	public static function nativeToken(): self {
		return new self( Config::NATIVE_TOKEN_ADDRESS );
	}
}
