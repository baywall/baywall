<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Application\Repository\JwtSecretKeyRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Application\ValueObject\AccessToken;
use Cornix\Serendipity\Core\Domain\ValueObject\UnixTimestamp;
use Cornix\Serendipity\Core\Infrastructure\JWT\JwtService;
use Cornix\Serendipity\Core\Infrastructure\JWT\ValueObject\Jwt;
use Cornix\Serendipity\Core\Infrastructure\JWT\ValueObject\JwtPayload;

class AccessTokenService {

	private JwtService $jwt_service;
	private JwtAlgorithmProvider $jwt_algorithm_provider;
	private JwtSecretKeyRepository $jwt_secret_key_repository;
	private AccessTokenExpirationProvider $expiration_provider;

	public function __construct( JwtService $jwt_service, JwtAlgorithmProvider $jwt_algorithm_provider, JwtSecretKeyRepository $jwt_secret_key_repository, AccessTokenExpirationProvider $access_token_expiration_provider ) {
		$this->jwt_service               = $jwt_service;
		$this->jwt_algorithm_provider    = $jwt_algorithm_provider;
		$this->jwt_secret_key_repository = $jwt_secret_key_repository;
		$this->expiration_provider       = $access_token_expiration_provider;
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
		$secret_key = $this->jwt_secret_key_repository->get();

		// JWTトークンを生成
		$jwt = $this->jwt_service->encode( $algorithm, $payload, $secret_key );

		// AccessTokenの型に変換して返す
		return AccessToken::from( $jwt->value() );
	}

	/** 指定したアクセストークンが有効かどうかを判定します */
	public function isValid( AccessToken $access_token ): bool {
		// 有効期限が現在時刻より後であれば有効
		// 署名の検証はdecode時に行われるためここでは不要
		return $this->decode( $access_token )->expiresAt()->value() > time();
	}

	/** 指定したアクセストークンからウォレットアドレスを取得します */
	public function getWalletAddress( AccessToken $access_token ): Address {
		return $this->decode( $access_token )->walletAddress();
	}

	private function decode( AccessToken $access_token ): JwtPayload {
		// Jwt型に変換
		$jwt = Jwt::from( $access_token->value() );

		// 署名用の秘密鍵を取得
		$secret_key = $this->jwt_secret_key_repository->get();

		return $this->jwt_service->decode( $jwt, $secret_key );
	}
}
