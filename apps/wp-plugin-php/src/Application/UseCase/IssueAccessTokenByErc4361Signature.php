<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Logging\AuditLogService;
use Baywall\Core\Application\Repository\Erc4361NonceRepository;
use Baywall\Core\Application\Service\Erc4361Service;
use Baywall\Core\Application\Service\TokenCookieService;
use Baywall\Core\Application\Service\TransactionRunner;
use Baywall\Core\Domain\Exception\ValidationFailedException;
use Baywall\Core\Domain\Service\SignatureService;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\Signature;

class IssueAccessTokenByErc4361Signature {
	public function __construct(
		private readonly TransactionRunner $transaction_runner,
		private readonly Erc4361Service $erc4361_service,
		private readonly Erc4361NonceRepository $erc4361_nonce_repository,
		private readonly SignatureService $signature_service,
		private readonly TokenCookieService $token_cookie_service,
		private readonly AuditLogService $audit_log_service
	) {}

	/** ERC-4361の署名を検証してアクセストークン(+リフレッシュトークン)を発行する */
	public function handle( Address $address, ChainId $chain_id, Signature $signature ): void {
		// 指定されたアドレスから、保存済みのnonceを取得
		$stored_nonce = $this->erc4361_nonce_repository->get( $address );

		if ( $stored_nonce === null || $this->erc4361_service->isNonceExpired( $stored_nonce ) ) {
			// nonce未保存又は期限切れの場合はエラー
			// ※ 第三者がリクエストを送信している可能性もあるため、保存済みnonce削除は行わない
			throw new ValidationFailedException( "[C8DF5CAB] ERC-4361 nonce not found or expired for address: {$address}" );
		}

		// 保存済みのnonceを使って署名用メッセージを再構築
		$message = $this->erc4361_service->createMessage( $address, $chain_id, $stored_nonce );
		// 再構築したメッセージと、受け取った署名からアドレスを計算
		$recovered_address = $this->signature_service->verifyMessage( $message, $signature );

		if ( ! $address->equals( $recovered_address ) ) {
			// 署名の検証に失敗した場合はエラー
			// ※ 第三者がリクエストを送信している可能性もあるため、保存済みnonce削除は行わない
			throw new ValidationFailedException( "[27FA5840] ERC-4361 signature verification failed for address: {$address}" );
		}

		$this->transaction_runner->transactional(
			function () use ( $address ) {
				// リフレッシュトークン/アクセストークンを発行し、クッキーに保存
				$this->token_cookie_service->issueAndSetCookies( $address );

				// 保存していたnonceをリポジトリから削除
				$this->erc4361_nonce_repository->delete( $address );
			}
		);

		// 非ログイン（ゲスト）のため user_id では区別できないので、実行者の wallet アドレスを message に入れる
		// 署名・nonceの値は記録しない
		$this->audit_log_service->info( 'auth_login_erc4361', "ERC-4361 login succeeded. address: {$address}, chainId: {$chain_id}" );
	}
}
