<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\Database\ValueObject;

use Baywall\Core\Domain\ValueObject\Interfaces\ValueObject;

class PrivateKeyEncryptionType implements ValueObject {

	// 以下の定義はDBに永続化されるため原則変更不可です。値は`chk_{table}_private_key`の条件と対応しており、付け替えると既存行の再マイグレーションが必要になります。
	// `0`は予約値（未指定・無効）のため使わず、値は1起点です。
	/** `PrivateKey::value()`の文字列をBase64エンコードした平文 */
	private const PLAIN_BASE64 = 1;
	/**
	 * `sodium_crypto_aead_xchacha20poly1305_ietf_encrypt()`による暗号文（nonce 24バイト + 認証タグ 16バイトを含む）をBase64エンコードした値
	 * （想定。実際のレイアウトと書式条件は暗号化の実装時に確定する）
	 */
	private const XCHACHA20_POLY1305_IETF = 2;

	private function __construct( int $private_key_enc_type_value ) {
		self::checkValidEncType( $private_key_enc_type_value );
		$this->value = $private_key_enc_type_value;
	}

	private readonly int $value;

	public function value(): int {
		return $this->value;
	}

	public function __toString(): string {
		return (string) $this->value;
	}

	public function equals( self $other ): bool {
		return $this->value === $other->value;
	}

	public static function from( int $private_key_enc_type_value ): self {
		return new self( $private_key_enc_type_value );
	}

	private static function checkValidEncType( int $private_key_enc_type_value ): void {
		if ( self::PLAIN_BASE64 !== $private_key_enc_type_value && self::XCHACHA20_POLY1305_IETF !== $private_key_enc_type_value ) {
			throw new \InvalidArgumentException( '[8D7D88FC] Invalid private key encryption type: ' . $private_key_enc_type_value );
		}
	}
}
