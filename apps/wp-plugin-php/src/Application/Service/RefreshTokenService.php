<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Application\Logging\AppLogger;
use Cornix\Serendipity\Core\Domain\Exception\UnauthorizedAccessException;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Infrastructure\Cookie\CookieWriter;
use Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject\RefreshToken;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\WpRefreshTokenRepository;

class RefreshTokenService {

	private AppLogger $app_logger;
	private WpRefreshTokenRepository $refresh_token_repository;
	private RefreshTokenCookieProvider $refresh_token_cookie_provider;
	private CookieWriter $cookie_writer;

	public function __construct(
		AppLogger $app_logger,
		WpRefreshTokenRepository $refresh_token_repository,
		RefreshTokenCookieProvider $refresh_token_cookie_provider,
		CookieWriter $cookie_writer
	) {
		$this->app_logger                    = $app_logger;
		$this->refresh_token_repository      = $refresh_token_repository;
		$this->refresh_token_cookie_provider = $refresh_token_cookie_provider;
		$this->cookie_writer                 = $cookie_writer;
	}

	/**
	 * トークンローテーションを実行します
	 *
	 * @param RefreshToken $refresh_token クライアントから送信されたリフレッシュトークン
	 */
	public function rotation( RefreshToken $refresh_token ): RotateRefreshTokenResult {

		$refresh_token_info = $this->refresh_token_repository->get( $refresh_token );

		// 以下の場合は例外をスロー
		// - データベースに存在しないリフレッシュトークン
		// - 無効化されたリフレッシュトークン
		// - 期限切れのリフレッシュトークン
		if ( $refresh_token_info === null || $refresh_token_info->isRevoked() || $refresh_token_info->isExpired() ) {
			$error = new UnauthorizedAccessException( '[6E4E2DAB] Invalid refresh token.' );
			$this->app_logger->error( $error );
			throw $error;
		}

		// 古いリフレッシュトークンを無効化
		$refresh_token_info->revoke();
		$this->refresh_token_repository->update( $refresh_token_info );

		// 新しいリフレッシュトークンを生成
		$new_refresh_token = RefreshToken::generate();
		// トークン所有者のウォレットアドレスを取得
		$wallet_address = $refresh_token_info->walletAddress();

		return new RotateRefreshTokenResult( $new_refresh_token, $wallet_address );
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

/**
 * RefreshTokenService::rotation の戻り値
 *
 * @internal
 */
class RotateRefreshTokenResult {
	public RefreshToken $new_refresh_token;
	public Address $wallet_address;

	public function __construct( RefreshToken $new_refresh_token, Address $wallet_address ) {
		$this->new_refresh_token = $new_refresh_token;
		$this->wallet_address    = $wallet_address;
	}
}
