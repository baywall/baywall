<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Domain\ValueObject\JwtSecretKey;

/** JWTの共通鍵を提供するインタフェース */
interface JwtSecretKeyProvider {
	/** JWTの共通鍵を取得します */
	public function get(): JwtSecretKey;
}
