<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Service;

use Baywall\Core\Application\ValueObject\Jwt;
use Baywall\Core\Application\ValueObject\JwtAlgorithm;
use Baywall\Core\Application\ValueObject\JwtPayload;
use Baywall\Core\Application\ValueObject\JwtSecretKey;

/** JWTのエンコード・デコードを行うインタフェース */
interface JwtService {
	public function encode( JwtAlgorithm $algorithm, JwtPayload $payload, JwtSecretKey $secret_key ): Jwt;

	public function decode( Jwt $jwt, JwtSecretKey $secret_key ): JwtPayload;
}
