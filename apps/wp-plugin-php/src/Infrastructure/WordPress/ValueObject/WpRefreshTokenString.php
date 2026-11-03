<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\ValueObject;

use Baywall\Core\Domain\ValueObject\Bytes;
use Baywall\Core\Domain\ValueObject\RefreshTokenString;
use DateTimeImmutable;

readonly class WpRefreshTokenString extends RefreshTokenString {
	private function __construct( string $wp_refresh_token_value ) {
		parent::__construct( $wp_refresh_token_value );
	}

	/**
	 * リフレッシュトークンの文字列を新規生成します
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
}
