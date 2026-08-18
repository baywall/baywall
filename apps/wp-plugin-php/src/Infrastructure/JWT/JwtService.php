<?php
declare(strict_types=1);

namespace Baywall\Core\Infrastructure\JWT;

use Baywall\Core\Infrastructure\JWT\ValueObject\Jwt;
use Baywall\Core\Infrastructure\JWT\ValueObject\JwtAlgorithm;
use Baywall\Core\Infrastructure\JWT\ValueObject\JwtPayload;
use Baywall\Core\Infrastructure\JWT\ValueObject\JwtSecretKey;
use Baywall\Core\Infrastructure\Reimpl\JWT\JwtCodec;

class JwtService {

	private JwtCodec $jwt_codec;

	public function __construct( JwtCodec $jwt_codec ) {
		$this->jwt_codec = $jwt_codec;
	}

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
