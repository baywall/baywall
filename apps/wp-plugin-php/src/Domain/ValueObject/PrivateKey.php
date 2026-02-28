<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Interfaces\ValueObject;

class PrivateKey implements ValueObject {

	private string $private_key_value;

	/** @disregard P1009 Undefined type */
	private function __construct(
		#[\SensitiveParameter]
		string $private_key_value
	) {
		// フォーマットチェック(32バイトの値)
		if ( ! preg_match( '/^0x[a-f0-9]{64}$/', $private_key_value ) ) {
			throw new \InvalidArgumentException( '[5CE68177] Invalid private key format: ' . $private_key_value );
		}
		$this->private_key_value = $private_key_value;
	}

	/**
	 * プライベートキーを16進数の文字列で取得します。
	 */
	public function value(): string {
		return $this->private_key_value;
	}

	/** @disregard P1009 Undefined type */
	public static function from(
		#[\SensitiveParameter]
		string $private_key_value
	): self {
		return new self( $private_key_value );
	}

	/**
	 * プライベートキーを文字列として返します。
	 */
	public function __toString(): string {
		return $this->private_key_value;
	}

	public function equals( self $other ): bool {
		// タイミング攻撃対策に`hash_equals`を使用
		return hash_equals( $this->private_key_value, $other->private_key_value );
	}

	public function __debugInfo() {
		return array(
			// プライベートキーはデバッグ出力から除外
			'private_key_value' => '*** sensitive data ***',
		);
	}
}
