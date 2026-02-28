<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use Cornix\Serendipity\Core\Constant\Config;
use Cornix\Serendipity\Core\Domain\ValueObject\Interfaces\ValueObject;
use Cornix\Serendipity\Core\Infrastructure\Format\Padding;
use Cornix\Serendipity\Core\Infrastructure\Util\Strings;

/**
 * アドレス(ウォレットアドレス/コントラクトアドレス)を表すクラス
 */
final class Address implements ValueObject {

	private function __construct( string $address_value ) {
		self::checkValidAddressFormat( $address_value );
		// アドレスは常にチェックサムアドレスで保持する
		$this->address_value = \Web3\Utils::toChecksumAddress( $address_value );
	}
	private string $address_value;

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
		return ( new Padding() )->toBytes32Hex( $this->value() );
	}

	public function __toString(): string {
		return $this->address_value;
	}

	public function equals( self $other ): bool {
		return $this->address_value === $other->address_value;
	}

	private static function checkValidAddressFormat( string $address_value ): void {
		// 本アプリでは`0x`プレフィックスを必須とする
		$is_valid = Strings::starts_with( $address_value, '0x' ) && \Web3\Utils::isAddress( $address_value );
		if ( ! $is_valid ) {
			throw new \InvalidArgumentException( '[B4AE59FC] Invalid address format. ' . $address_value );
		}
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
