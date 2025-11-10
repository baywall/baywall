<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Bytes;
use Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject\RefreshTokenHash;
use DateTimeImmutable;

/**
 * リフレッシュトークンを表すクラス
 *
 * MySQLのキーとして使うため特殊な文字列をリフレッシュトークンとして扱う。
 * フォーマット: `${yyyyMMddHHmmssSSS}.${256bitのランダムな16進文字列}`
 *
 * ハッシュ化する時は後ろの256bitのランダムな部分のみをハッシュ化する。
 */
class RefreshToken implements \Stringable {

	private const HASH_ALGORITHM = 'sha256';

	private string $token_value;

	private function __construct( string $token_value ) {
		$this->checkRefreshTokenValue( $token_value );
		assert( strlen( $token_value ) === ( 17 + 1 + 64 ), '[A0B07AA7]' );
		$this->token_value = $token_value;
	}

	public static function from( string $token_value ): self {
		return new self( $token_value );
	}

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

	public function value(): string {
		return $this->token_value;
	}

	/**
	 * データベースに保存する際のハッシュ化された文字列を取得します
	 */
	public function hash(): RefreshTokenHash {
		$parts           = explode( '.', $this->token_value );
		$timestamp_text  = $parts[0];
		$random_hex_text = $parts[1];

		$hashed_random_part = hash( self::HASH_ALGORITHM, $random_hex_text );

		return RefreshTokenHash::from( $timestamp_text . '.' . $hashed_random_part );
	}

	public function equals( self $other ): bool {
		return $this->token_value === $other->token_value;
	}

	public function __toString(): string {
		return $this->token_value;
	}

	private function checkRefreshTokenValue( string $token_value ): void {
		if ( ! preg_match( '/^\d{17}\.[0-9a-f]{64}$/', $token_value ) ) {
			throw new \InvalidArgumentException( '[1CFCEB6B] Invalid refresh token format: ' . $token_value );
		}
	}
}
