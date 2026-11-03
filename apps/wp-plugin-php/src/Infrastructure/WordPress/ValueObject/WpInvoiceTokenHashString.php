<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\ValueObject;

use Baywall\Core\Domain\ValueObject\Base\SecureStringValueObject;
use Baywall\Core\Domain\ValueObject\InvoiceTokenString;

readonly class WpInvoiceTokenHashString extends SecureStringValueObject {
	private const HASH_ALGORITHM = 'sha256';

	/** 入力トークン・生成ハッシュ双方が同一形式（`<17桁数字>.<sha256の小文字hex>`） */
	private const TOKEN_VALUE_PATTERN = '/^\d{17}\.[0-9a-f]{64}$/';

	private function __construct( string $wp_invoice_token_hash_value ) {
		parent::__construct( $wp_invoice_token_hash_value );
		self::checkWpInvoiceTokenHashFormat( $wp_invoice_token_hash_value );
	}

	public static function from( InvoiceTokenString $invoice_token_string ): self {
		// 外部入力がそのまま届くため合成前に形式を実検証する（`assert` は `zend.assertions=-1` で評価されないため使わない）
		if ( preg_match( self::TOKEN_VALUE_PATTERN, $invoice_token_string->value() ) !== 1 ) {
			throw new \InvalidArgumentException( '[EA028F00] Invalid invoice token format: ' . $invoice_token_string->value() );
		}

		$parts           = explode( '.', $invoice_token_string->value() );
		$timestamp_text  = $parts[0];
		$random_hex_text = $parts[1];

		// ランダム部分をハッシュ化
		$hashed_random_part = hash( self::HASH_ALGORITHM, $random_hex_text );

		return new self( $timestamp_text . '.' . $hashed_random_part );
	}

	/**
	 * WordPress環境で使用する請求書トークンハッシュのフォーマットチェック
	 *
	 * 結果的に`WpInvoiceTokenString`と同じチェックだが、一応別扱い
	 */
	private static function checkWpInvoiceTokenHashFormat( string $wp_invoice_token_hash_value ): void {
		if ( ! preg_match( self::TOKEN_VALUE_PATTERN, $wp_invoice_token_hash_value ) ) {
			throw new \InvalidArgumentException( '[56389772] Invalid invoice token hash format: ' . $wp_invoice_token_hash_value );
		}
	}
}
