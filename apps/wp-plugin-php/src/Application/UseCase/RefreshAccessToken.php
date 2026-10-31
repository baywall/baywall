<?php

declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Service\Clock;
use Baywall\Core\Application\Service\TokenCookieService;
use Baywall\Core\Application\Service\TransactionService;
use Baywall\Core\Domain\Service\RefreshTokenService;
use Baywall\Core\Domain\ValueObject\RefreshTokenString;

/**
 * アクセストークンを更新します
 */
class RefreshAccessToken {


	public function __construct(
		private readonly TransactionService $transaction_service,
		private readonly RefreshTokenService $refresh_token_service,
		private readonly TokenCookieService $token_cookie_service,
		private readonly Clock $clock
	) {}

	public function handle( string $refresh_token_value ): void {
		$now = $this->clock->now();

		// 古いリフレッシュトークンデータを削除
		// ※ タイミングはどこでもよいが、確実に動作するようにここで実行する。
		$this->refresh_token_service->purgeOldRepositoryData( $now );

		$this->transaction_service->transactional(
			function () use ( $refresh_token_value, $now ) {
				// トークンローテーションを実行
				$new_refresh_token = $this->refresh_token_service->rotation( RefreshTokenString::from( $refresh_token_value ), $now );

				// 新しいリフレッシュトークンとアクセストークンをクッキーに保存
				$this->token_cookie_service->setRefreshAndIssueAccess( $new_refresh_token );
			}
		);
	}
}
