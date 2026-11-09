<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Logging\AppLogService;
use Baywall\Core\Application\Port\Clock;
use Baywall\Core\Application\Service\TokenCookieService;
use Baywall\Core\Application\Service\TransactionRunner;
use Baywall\Core\Domain\Service\RefreshTokenService;
use Baywall\Core\Domain\ValueObject\RefreshTokenString;

/**
 * アクセストークンを更新します
 */
class RefreshAccessToken {
	public function __construct(
		private readonly AppLogService $app_log_service,
		private readonly TransactionRunner $transaction_runner,
		private readonly RefreshTokenService $refresh_token_service,
		private readonly TokenCookieService $token_cookie_service,
		private readonly Clock $clock
	) {}

	public function handle( string $refresh_token_value ): void {
		$now = $this->clock->now();

		// 古いリフレッシュトークンデータを削除
		// ※ タイミングはどこでもよいが、確実に動作するようにここで実行する。
		$this->refresh_token_service->purgeOldRepositoryData( $now );

		$new_refresh_token = $this->transaction_runner->transactional(
			function () use ( $refresh_token_value, $now ) {
				// トークンローテーションを実行
				$new_refresh_token = $this->refresh_token_service->rotation( RefreshTokenString::from( $refresh_token_value ), $now );

				// 新しいリフレッシュトークンとアクセストークンをクッキーに保存
				$this->token_cookie_service->setRefreshAndIssueAccess( $new_refresh_token );

				return $new_refresh_token;
			}
		);

		// トランザクション内で記録するとロールバック時に消失するため、確定後に記録する
		// リフレッシュトークンの値は機密値のため記録しない
		$this->app_log_service->info( "[C6FF2C69] Refresh token rotated. address: {$new_refresh_token->walletAddress()}" );
	}
}
