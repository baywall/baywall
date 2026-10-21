<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\JWT;

use Baywall\Core\Application\Service\JwtService;
use Baywall\Core\Application\ValueObject\Jwt;
use Baywall\Core\Application\ValueObject\JwtAlgorithm;
use Baywall\Core\Application\ValueObject\JwtPayload;
use Baywall\Core\Application\ValueObject\JwtSecretKey;
use Baywall\Core\Lib\Jwt\JwtCodec;

class JwtServiceImpl implements JwtService {


	public function __construct( private readonly JwtCodec $jwt_codec ) {}

	public function encode( JwtAlgorithm $algorithm, JwtPayload $payload, JwtSecretKey $secret_key ): Jwt {
		return Jwt::from(
			$this->jwt_codec->encode(
				$algorithm->value(),
				$payload->value(),
				$secret_key->value(),
			)
		);
	}

	public function decode( Jwt $jwt, JwtSecretKey $secret_key ): JwtPayload {
		$payload_array = $this->jwt_codec->decode(
			$jwt->value(),
			$secret_key->value(),
		);

		return JwtPayload::from( $payload_array );
	}
}
