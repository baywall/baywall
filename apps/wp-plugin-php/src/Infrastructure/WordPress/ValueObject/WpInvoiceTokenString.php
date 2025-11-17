<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Bytes;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceTokenString;

class WpInvoiceTokenString extends InvoiceTokenString {

	private const WP_INVOICE_TOKEN_LENGTH = 64; // 64文字（256bitを16進数で表現）

	private function __construct( string $wp_invoice_token_value ) {
		parent::__construct( $wp_invoice_token_value );
		self::checkWpInvoiceTokenFormat( $wp_invoice_token_value );
	}

	public static function from( string $wp_invoice_token_value ): self {
		return new self( $wp_invoice_token_value );
	}

	/** 請求書トークンの文字列を新規生成します */
	public static function generate(): self {
		$random_bytes = Bytes::generateRandom( self::WP_INVOICE_TOKEN_LENGTH * 4 / 8 ); // 64文字 => 256bit => 32byte
		return new self( bin2hex( $random_bytes->bin() ) );
	}

	/** WordPress環境で使用する請求書トークンのフォーマットチェック */
	private static function checkWpInvoiceTokenFormat( string $wp_invoice_token_value ): void {
		if ( ! preg_match( '/^[0-9a-f]{64}$/', $wp_invoice_token_value ) ) {
			throw new \InvalidArgumentException( '[954B0903] Invalid invoice token format: ' . $wp_invoice_token_value );
		}
	}
}
