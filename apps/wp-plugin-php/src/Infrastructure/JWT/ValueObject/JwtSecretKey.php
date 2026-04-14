<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\JWT\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Base\SecureStringValueObject;

/**
 * JWTで使用する共通鍵を表すクラス
 */
final class JwtSecretKey extends SecureStringValueObject {

	private function __construct( string $jwt_secret_key_value ) {
		parent::__construct( $jwt_secret_key_value );

		$this->checkJwtSecretKeyValue( $jwt_secret_key_value );
	}

	public static function from( string $jwt_secret_key_value ): self {
		return new self( $jwt_secret_key_value );
	}

	private function checkJwtSecretKeyValue( string $jwt_secret_key_value ): void {
		// 空文字の場合はエラー
		if ( empty( $jwt_secret_key_value ) ) {
			throw new \InvalidArgumentException( '[98936770] JWT secret key cannot be empty.' );
		}
	}
}
