<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\WordPress\ValueObject;

use Baywall\Core\Domain\ValueObject\Base\SecureStringValueObject;
use Baywall\Core\Domain\ValueObject\RefreshTokenString;

readonly class WpRefreshTokenHashString extends SecureStringValueObject {
	private const HASH_ALGORITHM = 'sha256';

	/** 入力トークン・生成ハッシュ双方が同一形式（`<17桁数字>.<sha256の小文字hex>`） */
	private const TOKEN_VALUE_PATTERN = '/^\d{17}\.[0-9a-f]{64}$/';

	private function __construct( string $wp_refresh_token_hash_value ) {
		parent::__construct( $wp_refresh_token_hash_value );
		self::checkWpRefreshTokenHashFormat( $wp_refresh_token_hash_value );
	}

	public static function from( RefreshTokenString $refresh_token_string ): self {
		// 外部入力がそのまま届くため合成前に形式を実検証する（`assert` は `zend.assertions=-1` で評価されないため使わない）
		if ( preg_match( self::TOKEN_VALUE_PATTERN, $refresh_token_string->value() ) !== 1 ) {
			throw new \InvalidArgumentException( '[D4C2C6E1] Invalid refresh token format: ' . $refresh_token_string->value() );
		}

		$parts           = explode( '.', $refresh_token_string->value() );
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
		if ( ! preg_match( self::TOKEN_VALUE_PATTERN, $wp_refresh_token_hash_value ) ) {
			throw new \InvalidArgumentException( '[3FFA8CF6] Invalid refresh token hash format: ' . $wp_refresh_token_hash_value );
		}
	}
}
