<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Logging\AppLogService;
use Baywall\Core\Application\Logging\AuditLogService;
use Baywall\Core\Application\Port\AccessTokenCookieProvider;
use Baywall\Core\Application\Port\Clock;
use Baywall\Core\Application\Port\RefreshTokenCookieProvider;
use Baywall\Core\Domain\Exception\AuthenticationFailedException;
use Baywall\Core\Domain\Service\RefreshTokenService;
use Baywall\Core\Domain\ValueObject\RefreshTokenString;
use Baywall\Core\Application\Port\CookieWriter;
use Baywall\Core\Domain\Repository\RefreshTokenRepository;

/**
 * ログアウト処理を行います。
 *
 * リフレッシュトークンの無効化と、refresh/access 両方の Cookie を期限切れにします。
 * refresh token が不在の場合も Cookie 失効処理は実行します（冪等）。
 */
class LogoutAuth {
	public function __construct(
		private readonly AppLogService $app_log_service,
		private readonly RefreshTokenRepository $refresh_token_repository,
		private readonly RefreshTokenService $refresh_token_service,
		private readonly RefreshTokenCookieProvider $refresh_token_cookie_provider,
		private readonly AccessTokenCookieProvider $access_token_cookie_provider,
		private readonly CookieWriter $cookie_writer,
		private readonly Clock $clock,
		private readonly AuditLogService $audit_log_service
	) {}

	/**
	 * ログアウト処理を実行します。
	 *
	 * @param string|null $refresh_token_value リフレッシュトークン文字列。null の場合は revoke をスキップします。
	 */
	public function handle( ?string $refresh_token_value ): void {
		$now = $this->clock->now();

		$refresh_token_wallet_address = null; // リフレッシュトークンに紐づくウォレットアドレス（ログ出力用）
		// リフレッシュトークンが存在する場合は無効化
		if ( $refresh_token_value !== null ) {
			try {
				$refresh_token_string         = RefreshTokenString::from( $refresh_token_value );
				$refresh_token_wallet_address = $this->refresh_token_repository->get( $refresh_token_string )?->walletAddress();
				$this->refresh_token_service->revoke( $refresh_token_string, $now );
			} catch ( \InvalidArgumentException | AuthenticationFailedException $e ) {
				$this->app_log_service->debug( $e );
			}
		}

		// リフレッシュトークン Cookie を期限切れにする
		$expired_refresh_cookie = $this->refresh_token_cookie_provider->getExpired();
		$success                = $this->cookie_writer->set( $expired_refresh_cookie );

		if ( $success === false ) {
			$error = new \RuntimeException( '[B276CD94] Failed to set expired refresh token cookie.' );
			$this->app_log_service->error( $error );
			throw $error;
		}

		// アクセストークン Cookie を期限切れにする
		$expired_access_cookie = $this->access_token_cookie_provider->getExpired();
		$success               = $this->cookie_writer->set( $expired_access_cookie );

		if ( $success === false ) {
			$error = new \RuntimeException( '[1F66F2EF] Failed to set expired access token cookie.' );
			$this->app_log_service->error( $error );
			throw $error;
		}

		$this->audit_log_service->info( 'auth_logout', "Logout succeeded. address: {$refresh_token_wallet_address}" );
	}
}
