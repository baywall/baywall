<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Base\SecureStringValueObject;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceTokenString;

class WpInvoiceTokenHashString extends SecureStringValueObject {
	private const HASH_ALGORITHM = 'sha256';

	private function __construct( string $wp_invoice_token_hash_value ) {
		parent::__construct( $wp_invoice_token_hash_value );
		self::checkWpInvoiceTokenHashFormat( $wp_invoice_token_hash_value );
	}

	public static function from( InvoiceTokenString $invoice_token_string ): self {
		assert( WpInvoiceTokenString::from( $invoice_token_string->value() ) instanceof WpInvoiceTokenString, '[81339409]' );
		// WpInvoiceTokenStringのフォーマット変更があった場合に気づけるようにassert文を追加
		assert( preg_match( '/^\d{17}\.[0-9a-f]{64}$/', $invoice_token_string->value() ) === 1, '[EA028F00]' );

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
		if ( ! preg_match( '/^\d{17}\.[0-9a-f]{64}$/', $wp_invoice_token_hash_value ) ) {
			throw new \InvalidArgumentException( '[56389772] Invalid invoice token hash format: ' . $wp_invoice_token_hash_value );
		}
	}
}
