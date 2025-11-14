<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\JWT;

use Cornix\Serendipity\Core\Infrastructure\JWT\ValueObject\Jwt;
use Cornix\Serendipity\Core\Infrastructure\JWT\ValueObject\JwtAlgorithm;
use Cornix\Serendipity\Core\Infrastructure\JWT\ValueObject\JwtPayload;
use Cornix\Serendipity\Core\Infrastructure\JWT\ValueObject\JwtSecretKey;
use Cornix\Serendipity\Core\Infrastructure\Reimpl\JWT\JwtCodec;

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
}
