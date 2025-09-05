<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use Cornix\Serendipity\Core\Infrastructure\Format\HexFormat;
use phpseclib\Math\BigInteger;
use yamaneyuta\Ulid;

/**
 * 請求書IDを表すクラス
 */
class InvoiceId implements \Stringable {

	private function __construct( Ulid $ulid ) {
		$this->ulid = $ulid;
	}
	private Ulid $ulid;

	/**
	 * 文字列(HEX/ULID)の請求書IDをオブジェクトに変換します。
	 *
	 * @param string|BigInteger $invoice_id_val 請求書ID
	 */
	public static function from( $invoice_id_val ): self {
		if ( is_string( $invoice_id_val ) ) {
			return new self( Ulid::from( $invoice_id_val ) );
		} elseif ( $invoice_id_val instanceof BigInteger ) {
			return new self( Ulid::from( HexFormat::toHex( $invoice_id_val ) ) );
		}
		throw new \InvalidArgumentException( '[DEE2905B] Invalid invoice ID. ' . var_export( $invoice_id_val, true ) );
	}
	public static function fromNullable( ?string $invoice_id_val ): ?self {
		return $invoice_id_val === null ? null : self::from( $invoice_id_val );
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
