<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Repository;

use Cornix\Serendipity\Core\Infrastructure\JWT\ValueObject\JwtSecretKey;

/** JWTの共通鍵を保管するインタフェース */
interface JwtSecretKeyRepository {
	/** JWTの共通鍵を取得します */
	public function get(): JwtSecretKey;

	/** JWTの共通鍵を保存します */
	public function save( JwtSecretKey $jwt_secret_key ): void;
}
