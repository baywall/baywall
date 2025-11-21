<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Base\StringValueObject;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceTokenString;

class WpInvoiceTokenHashString extends StringValueObject {
	private const HASH_ALGORITHM = 'sha256';

	private function __construct( string $wp_invoice_token_hash_value ) {
		parent::__construct( $wp_invoice_token_hash_value );
		self::checkWpInvoiceTokenHashFormat( $wp_invoice_token_hash_value );
	}

	public static function from( InvoiceTokenString $invoice_token_string ): self {
		assert( WpInvoiceTokenString::from( $invoice_token_string->value() ) instanceof WpInvoiceTokenString, '[81339409]' );

		return new self( hash( self::HASH_ALGORITHM, $invoice_token_string->value() ) );
	}

	/**
	 * WordPress環境で使用するリフレッシュトークンハッシュのフォーマットチェック
	 *
	 * 結果的に`WpInvoiceTokenString`と同じチェックだが、一応別扱い
	 */
	private static function checkWpInvoiceTokenHashFormat( string $wp_invoice_token_hash_value ): void {
		if ( ! preg_match( '/^[0-9a-f]{64}$/', $wp_invoice_token_hash_value ) ) {
			throw new \InvalidArgumentException( '[56389772] Invalid invoice token hash format: ' . $wp_invoice_token_hash_value );
		}
	}
}
