<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Service\AccessTokenService;
use Cornix\Serendipity\Core\Application\Service\RefreshTokenService;
use Cornix\Serendipity\Core\Application\Service\TransactionService;
use Cornix\Serendipity\Core\Infrastructure\WordPress\ValueObject\RefreshToken;

/**
 * アクセストークンを更新します
 */
class RefreshAccessToken {

	private TransactionService $transaction_service;
	private RefreshTokenService $refresh_token_service;
	private AccessTokenService $access_token_service;

	public function __construct(
		TransactionService $transaction_service,
		RefreshTokenService $refresh_token_service,
		AccessTokenService $access_token_service
	) {
		$this->transaction_service   = $transaction_service;
		$this->refresh_token_service = $refresh_token_service;
		$this->access_token_service  = $access_token_service;
	}

	public function handle( string $refresh_token_value ): string {
		return $this->transaction_service->transactional(
			function () use ( $refresh_token_value ) {
				// トークンローテーションを実行
				$rotate_result = $this->refresh_token_service->rotation( RefreshToken::from( $refresh_token_value ) );

				$new_refresh_token = $rotate_result->new_refresh_token; // トークンローテーションによって発行された新しいリフレッシュトークン
				$wallet_address    = $rotate_result->wallet_address; // 更新前のトークンを持っていたウォレットアドレス

				// 新しいリフレッシュトークンをクッキーに保存
				$this->refresh_token_service->setCookie( $new_refresh_token );

				// 新しいアクセストークンを発行
				$access_token = $this->access_token_service->issue( $wallet_address );

				// アクセストークンの値（文字列）を返す
				return $access_token->value();
			}
		);
	}
}
