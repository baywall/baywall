<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Infrastructure\JWT\ValueObject\JwtAlgorithm;

// /** JWTの署名アルゴリズムを提供するインタフェース */
interface JwtAlgorithmProvider {
	/** JWTの共通鍵を取得します */
	public function get(): JwtAlgorithm;
}
