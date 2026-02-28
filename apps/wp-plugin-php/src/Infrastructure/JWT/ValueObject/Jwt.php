<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\JWT\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Base\StringValueObject;

/**
 * JWT形式のトークンを表すクラス
 */
final class Jwt extends StringValueObject {

	private function __construct( string $jwt_value ) {
		parent::__construct( $jwt_value );
		$this->checkJwtFormat( $jwt_value );
	}

	public static function from( string $jwt_value ): self {
		return new self( $jwt_value );
	}

	private function checkJwtFormat( string $jwt_value ): void {
		// 簡易チェック。署名等はチェックしない
		if ( count( explode( '.', $jwt_value ) ) !== 3 ) {
			throw new \InvalidArgumentException( '[98936770] Invalid JWT access token format. ' . $jwt_value );
		}
	}
}
