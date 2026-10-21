<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Service;

use Baywall\Core\Application\ValueObject\JwtAlgorithm;

/** JWTの署名アルゴリズムを提供するインタフェース */
interface JwtAlgorithmProvider {
	/** JWTの署名アルゴリズムを取得します */
	public function get(): JwtAlgorithm;
}
