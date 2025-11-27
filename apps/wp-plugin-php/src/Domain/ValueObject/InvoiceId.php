<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use yamaneyuta\Ulid;

/**
 * 請求書IDを表すクラス
 */
class InvoiceId {

	private function __construct( Ulid $ulid ) {
		$this->ulid = $ulid;
	}
	private Ulid $ulid;

	public static function fromHex( Hex $hex ): self {
		return new self( Ulid::from( $hex->value() ) );
	}
	/** ULID形式の文字列からインスタンスを生成します */
	public static function fromUlidValue( string $ulid_value ): self {
		$ulid = Ulid::from( $ulid_value );
		assert( $ulid->toString() === $ulid_value, '[FB8865CF] Invalid ULID value: ' . $ulid_value );
		return new self( $ulid );
	}

	public static function fromUlidValueNullable( ?string $ulid_value ): ?self {
		return $ulid_value === null ? null : self::fromUlidValue( $ulid_value );
	}

	/**
	 * 新しい請求書IDを生成します。
	 */
	public static function generate(): self {
		return new self( new Ulid() );
	}

	/**
	 * `0x`プレフィックスを含むhex形式で値を取得します。
	 */
	public function hex(): string {
		return '0x' . $this->ulid->toHex();
	}

	/**
	 * ULID形式で値を取得します。
	 * ※ 基本的にDBに保存する際に使用します。
	 */
	public function ulid(): string {
		return $this->ulid->toString();
	}

	public function __toString(): string {
		return $this->ulid->toString();
	}
}
