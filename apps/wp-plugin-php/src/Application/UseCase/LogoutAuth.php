<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Logging\AppLogger;
use Cornix\Serendipity\Core\Application\Service\AccessTokenCookieProvider;
use Cornix\Serendipity\Core\Application\Service\RefreshTokenCookieProvider;
use Cornix\Serendipity\Core\Domain\Service\RefreshTokenService;
use Cornix\Serendipity\Core\Domain\ValueObject\RefreshTokenString;
use Cornix\Serendipity\Core\Infrastructure\Cookie\CookieWriter;

/**
 * ログアウト処理を行います。
 *
 * リフレッシュトークンの無効化と、refresh/access 両方の Cookie を期限切れにします。
 * refresh token が不在の場合も Cookie 失効処理は実行します（冪等）。
 */
class LogoutAuth {

	private AppLogger $app_logger;
	private RefreshTokenService $refresh_token_service;
	private RefreshTokenCookieProvider $refresh_token_cookie_provider;
	private AccessTokenCookieProvider $access_token_cookie_provider;
	private CookieWriter $cookie_writer;

	public function __construct(
		AppLogger $app_logger,
		RefreshTokenService $refresh_token_service,
		RefreshTokenCookieProvider $refresh_token_cookie_provider,
		AccessTokenCookieProvider $access_token_cookie_provider,
		CookieWriter $cookie_writer
	) {
		$this->app_logger                    = $app_logger;
		$this->refresh_token_service         = $refresh_token_service;
		$this->refresh_token_cookie_provider = $refresh_token_cookie_provider;
		$this->access_token_cookie_provider  = $access_token_cookie_provider;
		$this->cookie_writer                 = $cookie_writer;
	}

	/**
	 * ログアウト処理を実行します。
	 *
	 * @param string|null $refresh_token_value リフレッシュトークン文字列。null の場合は revoke をスキップします。
	 */
	public function handle( ?string $refresh_token_value ): void {

		// リフレッシュトークンが存在する場合は無効化
		if ( $refresh_token_value !== null ) {
			$this->refresh_token_service->revoke( RefreshTokenString::from( $refresh_token_value ) );
		}

		// リフレッシュトークン Cookie を期限切れにする
		$expired_refresh_cookie = $this->refresh_token_cookie_provider->getExpired();
		$success                = $this->cookie_writer->set( $expired_refresh_cookie );

		if ( $success === false ) {
			$error = new \RuntimeException( '[B276CD94] Failed to set expired refresh token cookie.' );
			$this->app_logger->error( $error );
			throw $error;
		}

		// アクセストークン Cookie を期限切れにする
		$expired_access_cookie = $this->access_token_cookie_provider->getExpired();
		$success               = $this->cookie_writer->set( $expired_access_cookie );

		if ( $success === false ) {
			$error = new \RuntimeException( '[1F66F2EF] Failed to set expired access token cookie.' );
			$this->app_logger->error( $error );
			throw $error;
		}
	}
}
