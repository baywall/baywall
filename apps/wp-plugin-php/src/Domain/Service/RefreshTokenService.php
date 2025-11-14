<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Service;

use Cornix\Serendipity\Core\Domain\Entity\RefreshToken;
use Cornix\Serendipity\Core\Domain\Exception\UnauthorizedAccessException;
use Cornix\Serendipity\Core\Domain\Repository\RefreshTokenRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\RefreshTokenString;

abstract class RefreshTokenService {

	/** リフレッシュトークン文字列を生成します */
	abstract protected function generateRefreshTokenString(): RefreshTokenString;

	private RefreshTokenRepository $refresh_token_repository;

	protected function __construct( RefreshTokenRepository $refresh_token_repository ) {
		$this->refresh_token_repository = $refresh_token_repository;
	}

	/**
	 * トークンローテーションを実行します
	 *
	 * @param RefreshTokenString $refresh_token_string クライアントから送信されたリフレッシュトークン文字列
	 */
	public function rotation( RefreshTokenString $refresh_token_string ): RefreshToken {

		$refresh_token = $this->refresh_token_repository->get( $refresh_token_string );

		// 以下の場合は例外をスロー
		// - データベースに存在しないリフレッシュトークン
		// - 無効化されたリフレッシュトークン
		// - 期限切れのリフレッシュトークン
		if ( $refresh_token === null || $refresh_token->isRevoked() || $refresh_token->isExpired() ) {
			throw new UnauthorizedAccessException( '[6E4E2DAB] Invalid refresh token.' );
		}

		// 古いリフレッシュトークンを無効化
		$refresh_token->revoke();
		$this->refresh_token_repository->update( $refresh_token );

		// 新しいリフレッシュトークンを生成して保存
		$new_refresh_token = RefreshToken::create(
			$this->generateRefreshTokenString(),
			$refresh_token->walletAddress(),
			$refresh_token->expiresAt(),
			null // リフレッシュトークン生成時、`revoked_at`はnullに設定
		);
		$this->refresh_token_repository->add( $new_refresh_token );

		return $new_refresh_token;
	}
}
