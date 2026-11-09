<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Logging\AppLogService;
use Baywall\Core\Application\Port\InvoiceTokenCookieProvider;
use Baywall\Core\Application\Port\Clock;
use Baywall\Core\Application\Port\CookieWriter;
use Baywall\Core\Application\Service\TransactionRunner;
use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Application\UseCase\InitCrawledBlockNumber;
use Baywall\Core\Domain\Exception\ValidationFailedException;
use Baywall\Core\Domain\Repository\TokenRepository;
use Baywall\Core\Domain\Service\InvoiceService;
use Baywall\Core\Domain\Service\InvoiceTokenService;
use Baywall\Core\Domain\Service\ServerSignerService;
use Baywall\Core\Domain\Service\SiteStatusService;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\PostId;

class IssueInvoice {
	public function __construct(
		private readonly AppLogService $app_log_service,
		private readonly UserAccessChecker $user_access_checker,
		private readonly TransactionRunner $transaction_runner,
		private readonly InvoiceService $invoice_service,
		private readonly TokenRepository $token_repository,
		private readonly ServerSignerService $server_signer_service,
		private readonly InitCrawledBlockNumber $init_crawled_block_number,
		private readonly InvoiceTokenService $invoice_token_service,
		private readonly InvoiceTokenCookieProvider $invoice_token_cookie_provider,
		private readonly CookieWriter $cookie_writer,
		private readonly SiteStatusService $site_status_service,
		private readonly Clock $clock
	) {}

	/**
	 * 請求書を発行し、サーバ署名と InvoiceToken Cookie を設定して発行結果を返します
	 *
	 * @return array{message: string, signature: string, paymentAmount: string}
	 */
	public function handle( PostId $post_id, ChainId $chain_id, Address $token_address, Address $buyer_address ): array {
		// 投稿を閲覧できる権限があることをチェック
		$this->user_access_checker->checkCanViewPost( $post_id );

		// 取引が一時停止中の時は請求書発行を拒否（フロントエンドではボタンを無効化するため例外スローで対応）
		if ( $this->site_status_service->isTransactionSuspended() ) {
			throw new ValidationFailedException( '[55EC126B] Transaction is suspended.' );
		}

		$now           = $this->clock->now();
		$payment_token = $this->token_repository->get( $chain_id, $token_address );
		if ( $payment_token === null ) {
			throw new \InvalidArgumentException( "[BA148318] Payment token not found for chain ID: {$chain_id} and address: {$token_address}" );
		}

		// 古い請求書トークンデータを削除
		// ※ タイミングはどこでもよいが、確実に動作するようにここで実行する。
		$this->invoice_token_service->purgeOldRepositoryData( $now );

		// 請求書番号を発行(+現在の販売価格を記録)
		$result = $this->transaction_runner->transactional(
			function () use ( $post_id, $payment_token, $buyer_address, $now ) {
				// 請求書を作成
				$invoice = $this->invoice_service->issueInvoice( $buyer_address, $post_id, $payment_token, $now );

				// 販売価格が0でないのに支払額が0になっている場合はエラー（fail safe）
				$selling_amount = $invoice->sellingPrice()->amount();
				$payment_amount = $invoice->paymentAmount();
				if ( ! $selling_amount->isZero() && $payment_amount->isZero() ) {
					throw new \RuntimeException( "[9E978B09] Payment amount is zero for non-zero selling price. post_id: {$post_id}, payment_token: {$payment_token}, payment_amount: {$payment_amount}" );
				}

				// 署名対象となるメッセージを作成
				$signing_message_bytes = $this->server_signer_service->createSigningMessageBytes( $invoice );
				// メッセージに対して署名
				$signature = $this->server_signer_service->signMessageBytes( $signing_message_bytes );

				// クロール済みブロック番号を初期化
				$this->init_crawled_block_number->handle( $payment_token->chainId()->value() );

				// 請求書トークンを発行し、Cookieに保存
				$invoice_token = $this->invoice_token_service->issue( $invoice->id() );
				$cookie        = $this->invoice_token_cookie_provider->get( $invoice_token );
				$this->cookie_writer->set( $cookie );

				return array(
					'message'       => $signing_message_bytes->hex()->value(),
					'signature'     => $signature->hex()->value(),
					'paymentAmount' => $invoice->paymentAmount()->value(),
				);
			}
		);

		// トランザクション内で記録するとロールバック時に消失するため、確定後に記録する
		// 請求書トークンと署名は機密値のため記録しない
		$this->app_log_service->info( "[FA4AFCCF] Invoice issued. postId: {$post_id}, chainId: {$chain_id}, tokenAddress: {$token_address}, buyerAddress: {$buyer_address}" );

		return $result;
	}
}
