<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Service;

use Baywall\Core\Infrastructure\JWT\ValueObject\JwtAlgorithm;

// /** JWTの署名アルゴリズムを提供するインタフェース */
interface JwtAlgorithmProvider {
	/** JWTの共通鍵を取得します */
	public function get(): JwtAlgorithm;
}
