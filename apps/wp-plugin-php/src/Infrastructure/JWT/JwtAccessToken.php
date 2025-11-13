<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\JWT;

use Cornix\Serendipity\Core\Domain\ValueObject\Base\AccessToken;

/**
 * JWT形式のアクセストークンを表すクラス
 */
final class JwtAccessToken extends AccessToken {

	private function __construct( string $jwt_access_token_value ) {
		parent::__construct( $jwt_access_token_value );

		$this->checkJwtFormat( $jwt_access_token_value );
	}

	public static function from( string $jwt_access_token_value ): self {
		return new self( $jwt_access_token_value );
	}

	private function checkJwtFormat( string $jwt_access_token_value ): void {
		// 簡易チェック。署名等はチェックしない
		if ( count( explode( '.', $jwt_access_token_value ) ) !== 3 ) {
			throw new \InvalidArgumentException( '[98936770] Invalid JWT access token format. ' . $jwt_access_token_value );
		}
	}
}
