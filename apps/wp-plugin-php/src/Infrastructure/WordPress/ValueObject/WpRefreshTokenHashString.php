<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Base\StringValueObject;

class WpRefreshTokenHashString extends StringValueObject {
	private const HASH_ALGORITHM = 'sha256';

	private function __construct( string $wp_refresh_token_hash_value ) {
		parent::__construct( $wp_refresh_token_hash_value );
		self::checkWpRefreshTokenHashFormat( $wp_refresh_token_hash_value );
	}

	public static function from( WpRefreshTokenString $wp_refresh_token_string ): self {
		// WpRefreshTokenStringのフォーマット変更があった場合に気づけるようにassert文を追加
		assert( preg_match( '/^\d{17}\.[0-9a-f]{64}$/', $wp_refresh_token_string->value() ) === 1, '[D4C2C6E1]' );

		$parts           = explode( '.', $wp_refresh_token_string->value() );
		$timestamp_text  = $parts[0];
		$random_hex_text = $parts[1];

		// ランダム部分をハッシュ化
		$hashed_random_part = hash( self::HASH_ALGORITHM, $random_hex_text );

		return new self( $timestamp_text . '.' . $hashed_random_part );
	}

	/**
	 * WordPress環境で使用するリフレッシュトークンハッシュのフォーマットチェック
	 *
	 * 結果的に`WpRefreshTokenString`と同じチェックだが、一応別扱い
	 */
	private static function checkWpRefreshTokenHashFormat( string $wp_refresh_token_hash_value ): void {
		if ( ! preg_match( '/^\d{17}\.[0-9a-f]{64}$/', $wp_refresh_token_hash_value ) ) {
			throw new \InvalidArgumentException( '[3FFA8CF6] Invalid refresh token hash format: ' . $wp_refresh_token_hash_value );
		}
	}
}
