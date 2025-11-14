<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Application\ValueObject\AccessToken;
use Cornix\Serendipity\Core\Domain\ValueObject\UnixTimestamp;
use Cornix\Serendipity\Core\Infrastructure\JWT\JwtService;
use Cornix\Serendipity\Core\Infrastructure\JWT\ValueObject\JwtPayload;

class AccessTokenService {

	private JwtService $jwt_service;
	private JwtAlgorithmProvider $jwt_algorithm_provider;
	private JwtSecretKeyProvider $jwt_secret_key_provider;
	private AccessTokenExpirationProvider $expiration_provider;

	public function __construct( JwtService $jwt_service, JwtAlgorithmProvider $jwt_algorithm_provider, JwtSecretKeyProvider $jwt_secret_key_provider, AccessTokenExpirationProvider $access_token_expiration_provider ) {
		$this->jwt_service             = $jwt_service;
		$this->jwt_algorithm_provider  = $jwt_algorithm_provider;
		$this->jwt_secret_key_provider = $jwt_secret_key_provider;
		$this->expiration_provider     = $access_token_expiration_provider;
	}

	/** 指定したウォレットアドレスに対するアクセストークンを発行します */
	public function issue( Address $wallet_address ): AccessToken {
		// 署名アルゴリズムを取得
		$algorithm = $this->jwt_algorithm_provider->get();

		// ペイロードを作成
		$payload = JwtPayload::create(
			$wallet_address,
			UnixTimestamp::now(), // 発行日時
			$this->expiration_provider->get(), // 有効期限
		);

		// 署名用の秘密鍵を取得
		$secret_key = $this->jwt_secret_key_provider->get();

		// JWTトークンを生成
		$jwt = $this->jwt_service->encode( $algorithm, $payload, $secret_key );

		// AccessTokenの型に変換して返す
		return AccessToken::from( $jwt->value() );
	}
}
