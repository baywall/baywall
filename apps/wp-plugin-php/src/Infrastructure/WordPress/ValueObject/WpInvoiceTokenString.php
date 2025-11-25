<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Bytes;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceTokenString;
use DateTimeImmutable;

class WpInvoiceTokenString extends InvoiceTokenString {

	private function __construct( string $wp_invoice_token_value ) {
		parent::__construct( $wp_invoice_token_value );
		self::checkWpInvoiceTokenFormat( $wp_invoice_token_value );
	}

	public static function from( string $wp_invoice_token_value ): self {
		return new self( $wp_invoice_token_value );
	}

	/**
	 * 請求書トークンの文字列を新規生成します
	 *
	 * フォーマット: `yyyyMMddHHmmssSSS`.`64桁の16進数文字列`
	 */
	public static function generate(): self {
		$random_bytes = Bytes::generateRandom( 32 ); // 256bit
		$now          = new DateTimeImmutable();

		// - `YmdHis` で `yyyyMMddHHmmss` と同等
		// => https://www.php.net/manual/en/datetime.format.php#refsect1-datetime.format-parameters
		// - $now->format( 'u' ) はマイクロ秒 (6桁) を返すため、ミリ秒 (3桁) に変換するには1000で割る
		$token_value =
			$now->format( 'YmdHis' )
			. sprintf( '%03d', (int) ( (int) $now->format( 'u' ) / 1000 ) )
			. '.'
			. str_replace( '0x', '', $random_bytes->hex()->value() );

		return new self( $token_value );
	}

	/** WordPress環境で使用する請求書トークンのフォーマットチェック */
	private static function checkWpInvoiceTokenFormat( string $wp_invoice_token_value ): void {
		if ( ! preg_match( '/^\d{17}\.[0-9a-f]{64}$/', $wp_invoice_token_value ) ) {
			throw new \InvalidArgumentException( '[954B0903] Invalid invoice token format: ' . $wp_invoice_token_value );
		}
	}
}
