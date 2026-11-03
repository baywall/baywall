<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Port;

use Baywall\Core\Application\ValueObject\JwtAlgorithm;

/** JWTの署名アルゴリズムを提供するインタフェース */
interface JwtAlgorithmProvider {
	/** JWTの署名アルゴリズムを取得します */
	public function get(): JwtAlgorithm;
}
