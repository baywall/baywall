<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Service;

use Baywall\Core\Application\Port\AccessTokenExpirationProvider;
use Baywall\Core\Application\Port\Clock;
use Baywall\Core\Application\Port\JwtAlgorithmProvider;
use Baywall\Core\Application\Port\JwtService;
use Baywall\Core\Application\Repository\JwtSecretKeyRepository;
use Baywall\Core\Domain\Exception\AuthenticationFailedException;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Application\ValueObject\AccessToken;
use Baywall\Core\Application\ValueObject\Jwt;
use Baywall\Core\Application\ValueObject\JwtPayload;

class AccessTokenService {

	private readonly JwtService $jwt_service;
	private readonly JwtAlgorithmProvider $jwt_algorithm_provider;
	private readonly JwtSecretKeyRepository $jwt_secret_key_repository;
	private readonly AccessTokenExpirationProvider $access_token_expiration_provider;
	private readonly Clock $clock;

	public function __construct( JwtService $jwt_service, JwtAlgorithmProvider $jwt_algorithm_provider, JwtSecretKeyRepository $jwt_secret_key_repository, AccessTokenExpirationProvider $access_token_expiration_provider, Clock $clock ) {
		$this->jwt_service                      = $jwt_service;
		$this->jwt_algorithm_provider           = $jwt_algorithm_provider;
		$this->jwt_secret_key_repository        = $jwt_secret_key_repository;
		$this->access_token_expiration_provider = $access_token_expiration_provider;
		$this->clock                            = $clock;
	}

	/** 指定したウォレットアドレスに対するアクセストークンを発行します */
	public function issue( Address $wallet_address ): AccessToken {
		// 署名アルゴリズムを取得
		$algorithm = $this->jwt_algorithm_provider->get();

		// ペイロードを作成
		$payload = JwtPayload::create(
			$wallet_address,
			$this->clock->now(), // 発行日時
			$this->access_token_expiration_provider->get(), // 有効期限
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
		return $this->decode( $access_token )->expiresAt()->value() > $this->clock->now()->value();
	}

	/** 指定したアクセストークンからウォレットアドレスを取得します */
	public function getWalletAddress( AccessToken $access_token ): Address {
		return $this->decode( $access_token )->walletAddress();
	}

	/** @throws AuthenticationFailedException トークンの形式不正・署名検証失敗などデコードに失敗した場合 */
	private function decode( AccessToken $access_token ): JwtPayload {
		// 認証不能は401に統一する。元例外は $previous で保持し、ログから原因を切り分けられるようにする
		try {
			// Jwt型に変換
			$jwt = Jwt::from( $access_token->value() );

			// 署名用の秘密鍵を取得
			$secret_key = $this->jwt_secret_key_repository->get();

			return $this->jwt_service->decode( $jwt, $secret_key );
		} catch ( \InvalidArgumentException $e ) {
			throw new AuthenticationFailedException( '[21E94F4F] ' . $e->getMessage(), 0, $e );
		}
	}
}
