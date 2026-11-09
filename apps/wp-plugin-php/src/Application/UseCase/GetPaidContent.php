<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Logging\AuditLogService;
use Baywall\Core\Application\Port\AccessTokenRequestProvider;
use Baywall\Core\Application\Service\AccessTokenService;
use Baywall\Core\Application\Service\PaymentVerifier;
use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Application\ValueObject\AccessToken;
use Baywall\Core\Domain\Exception\PaymentNotCompletedException;
use Baywall\Core\Domain\Exception\AuthenticationFailedException;
use Baywall\Core\Domain\Repository\PostRepository;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\PostId;

class GetPaidContent {
	public function __construct(
		private readonly UserAccessChecker $user_access_checker,
		private readonly AccessTokenRequestProvider $access_token_request_provider,
		private readonly AccessTokenService $access_token_service,
		private readonly PostRepository $post_repository,
		private readonly PaymentVerifier $payment_verifier,
		private readonly AuditLogService $audit_log_service
	) {}

	public function handle( int $post_id_value ): string {
		$post_id = PostId::from( $post_id_value );

		$access_token = $this->resolveAccessToken();
		$this->checkCanViewPost( $post_id );
		$address = $this->resolveWalletAddress( $access_token );

		if ( ! $this->payment_verifier->isPaid( $post_id, $address ) ) {
			throw new PaymentNotCompletedException( "[E868EFEF] is_paid is false : {$post_id}" );
		}
		$paid_content = $this->getPaidContentValue( $post_id );
		$this->audit_log_service->info( 'paid_content_served', "Paid content served. postId: {$post_id}, walletAddress: {$address}" );
		return $paid_content;
	}

	private function resolveAccessToken(): AccessToken {
		$access_token_value = $this->access_token_request_provider->get();
		if ( $access_token_value === null ) {
			throw new AuthenticationFailedException( '[8AD8257F] Access token is missing.' );
		}
		$access_token = AccessToken::from( $access_token_value );

		// 署名が不正な場合はdecode時に例外がスローされる
		if ( ! $this->access_token_service->isValid( $access_token ) ) {
			throw new AuthenticationFailedException( '[3975F78C] Access token is invalid.' );
		}
		return $access_token;
	}

	private function checkCanViewPost( PostId $post_id ): void {
		// アクセストークンで認証済みが確定しているため、ヒント(true)を渡して閲覧権限をチェック
		$this->user_access_checker->checkCanViewPost( $post_id, true );
	}

	private function resolveWalletAddress( AccessToken $access_token ): Address {
		return $this->access_token_service->getWalletAddress( $access_token );
	}

	private function getPaidContentValue( PostId $post_id ): string {
		$paid_content = $this->post_repository->get( $post_id )->paidContent();
		if ( null === $paid_content ) {
			// 支払い確認済みなのに有料部分が未設定は想定外のデータ状態
			throw new \LogicException( "[8301ADB2] Paid content is null for post ID: {$post_id}" );
		}
		return $paid_content->value();
	}
}
