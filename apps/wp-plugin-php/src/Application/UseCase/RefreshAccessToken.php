<?php

declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Logging\AppLogger;
use Cornix\Serendipity\Core\Application\Service\AccessTokenCookieProvider;
use Cornix\Serendipity\Core\Application\Service\AccessTokenService;
use Cornix\Serendipity\Core\Application\Service\RefreshTokenCookieProvider;
use Cornix\Serendipity\Core\Application\Service\TransactionService;
use Cornix\Serendipity\Core\Domain\Entity\RefreshToken;
use Cornix\Serendipity\Core\Domain\Service\RefreshTokenService;
use Cornix\Serendipity\Core\Domain\ValueObject\RefreshTokenString;
use Cornix\Serendipity\Core\Infrastructure\Cookie\CookieWriter;

/**
 * アクセストークンを更新します
 */
class RefreshAccessToken {


	private AppLogger $app_logger;
	private TransactionService $transaction_service;
	private RefreshTokenService $refresh_token_service;
	private RefreshTokenCookieProvider $refresh_token_cookie_provider;
	private AccessTokenCookieProvider $access_token_cookie_provider;
	private AccessTokenService $access_token_service;
	private CookieWriter $cookie_writer;

	public function __construct(
		AppLogger $app_logger,
		TransactionService $transaction_service,
		RefreshTokenService $refresh_token_service,
		RefreshTokenCookieProvider $refresh_token_cookie_provider,
		AccessTokenCookieProvider $access_token_cookie_provider,
		AccessTokenService $access_token_service,
		CookieWriter $cookie_writer
	) {
		$this->app_logger                    = $app_logger;
		$this->transaction_service           = $transaction_service;
		$this->refresh_token_service         = $refresh_token_service;
		$this->refresh_token_cookie_provider = $refresh_token_cookie_provider;
		$this->access_token_cookie_provider  = $access_token_cookie_provider;
		$this->access_token_service          = $access_token_service;
		$this->cookie_writer                 = $cookie_writer;
	}

	public function handle( string $refresh_token_value ): void {
		$this->transaction_service->transactional(
			function () use ( $refresh_token_value ) {
				// トークンローテーションを実行
				$new_refresh_token = $this->refresh_token_service->rotation( RefreshTokenString::from( $refresh_token_value ) );

				// 新しいリフレッシュトークンをクッキーに保存
				$this->setCookie( $new_refresh_token );

				// 新しいアクセストークンを発行
				$access_token        = $this->access_token_service->issue( $new_refresh_token->walletAddress() );
				$access_token_cookie = $this->access_token_cookie_provider->get( $access_token );
				$success             = $this->cookie_writer->set( $access_token_cookie );

				if ( $success === false ) {
					$error = new \RuntimeException( '[02DC84AF] Failed to set access token cookie.' );
					$this->app_logger->error( $error );
					throw $error;
				}
			}
		);
	}


	/**
	 * リフレッシュトークンをクッキーに保存します
	 *
	 * @param RefreshToken $new_refresh_token 新しいリフレッシュトークン
	 */
	public function setCookie( RefreshToken $new_refresh_token ): void {
		$cookie  = $this->refresh_token_cookie_provider->get( $new_refresh_token );
		$success = $this->cookie_writer->set( $cookie );

		if ( $success === false ) {
			$error = new \RuntimeException( '[68ACEBC4] Failed to set refresh token cookie.' );
			$this->app_logger->error( $error );
			throw $error;
		}
	}
}
