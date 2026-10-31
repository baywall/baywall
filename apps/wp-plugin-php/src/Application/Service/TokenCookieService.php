<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Service;

use Baywall\Core\Domain\Entity\RefreshToken;
use Baywall\Core\Domain\Service\RefreshTokenService;
use Baywall\Core\Domain\ValueObject\Address;

/**
 * リフレッシュトークン/アクセストークンの発行とCookieへの書き込みをまとめたサービス
 *
 * ERC-4361署名・請求書トークン・リフレッシュの3経路で重複していた処理を集約する。
 */
class TokenCookieService {


	public function __construct(
		private readonly RefreshTokenService $refresh_token_service,
		private readonly RefreshTokenCookieProvider $refresh_token_cookie_provider,
		private readonly AccessTokenService $access_token_service,
		private readonly AccessTokenCookieProvider $access_token_cookie_provider,
		private readonly CookieWriter $cookie_writer
	) {}

	/** リフレッシュトークンを発行し、リフレッシュ/アクセストークンをCookieへ書き込む */
	public function issueAndSetCookies( Address $wallet_address ): void {
		$this->setRefreshAndIssueAccess( $this->refresh_token_service->issue( $wallet_address ) );
	}

	/** 発行済みリフレッシュトークンをCookieへ書き込み、アクセストークンを発行してCookieへ書き込む */
	public function setRefreshAndIssueAccess( RefreshToken $refresh_token ): void {
		// 書き込み失敗時は [68ACEBC4] / [02DC84AF] の RuntimeException（RefreshAccessToken 現行の挙動を維持）
		$refresh_token_cookie = $this->refresh_token_cookie_provider->get( $refresh_token );
		if ( $this->cookie_writer->set( $refresh_token_cookie ) === false ) {
			throw new \RuntimeException( '[68ACEBC4] Failed to set refresh token cookie.' );
		}

		$access_token        = $this->access_token_service->issue( $refresh_token->walletAddress() );
		$access_token_cookie = $this->access_token_cookie_provider->get( $access_token );
		if ( $this->cookie_writer->set( $access_token_cookie ) === false ) {
			throw new \RuntimeException( '[02DC84AF] Failed to set access token cookie.' );
		}
	}
}
