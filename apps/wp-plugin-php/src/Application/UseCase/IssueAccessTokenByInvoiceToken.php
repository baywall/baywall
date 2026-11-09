<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Logging\AppLogService;
use Baywall\Core\Application\Logging\AuditLogService;
use Baywall\Core\Application\Service\AppContractCrawlService;
use Baywall\Core\Application\Port\Clock;
use Baywall\Core\Application\Service\ConfirmationsService;
use Baywall\Core\Application\Port\InvoiceTokenCookieProvider;
use Baywall\Core\Application\Service\TokenCookieService;
use Baywall\Core\Domain\Exception\PaymentNotCompletedException;
use Baywall\Core\Domain\Repository\ChainRepository;
use Baywall\Core\Domain\Service\InvoiceService;
use Baywall\Core\Domain\Service\InvoiceTokenService;
use Baywall\Core\Domain\ValueObject\InvoiceTokenString;
use Baywall\Core\Application\Port\CookieWriter;

/**
 * 請求書トークンとアクセストークン(+リフレッシュトークン)の引き換えを行うクラス
 *
 * ここでは、トランザクションがブロックに存在するかどうかで請求書トークンの持ち主の確定を行っています。
 * サーバーが指定した待機ブロック数については考慮していないことに注意してください。
 * ※ サーバー指定の待機ブロック数は有料記事取得時に判定します
 */
class IssueAccessTokenByInvoiceToken {
	public function __construct(
		private readonly AppLogService $app_log_service,
		private readonly TokenCookieService $token_cookie_service,
		private readonly CookieWriter $cookie_writer,
		private readonly InvoiceTokenService $invoice_token_service,
		private readonly InvoiceTokenCookieProvider $invoice_token_cookie_provider,
		private readonly InvoiceService $invoice_service,
		private readonly ConfirmationsService $confirmations_service,
		private readonly ChainRepository $chain_repository,
		private readonly AppContractCrawlService $app_contract_crawl_service,
		private readonly Clock $clock,
		private readonly AuditLogService $audit_log_service
	) {}

	public function handle( string $invoice_token_string_value ): void {
		$now                  = $this->clock->now();
		$invoice_token_string = InvoiceTokenString::from( $invoice_token_string_value );
		// 請求書トークンの文字列から発行した請求書を取得
		$invoice = $this->invoice_service->getByInvoiceTokenString( $invoice_token_string );

		// 支払いの確認が取れたかどうかを取得
		$is_confirmed = $this->confirmations_service->isConfirmed( $invoice->chainId(), $invoice->postId(), $invoice->buyerAddress() );

		if ( $is_confirmed === false ) {
			// （まだ）支払いが確認できない場合は請求書トークンのローテーションを行い、例外をスロー
			// ※この後ブロックに取り込まれる可能性もあるのでCookieの無効化は行わない

			// 請求書トークンのローテーション(DB更新+Cookie書き込み)
			$new_invoice_token        = $this->invoice_token_service->rotation( $invoice_token_string, $now );
			$new_invoice_token_cookie = $this->invoice_token_cookie_provider->get( $new_invoice_token );
			$this->cookie_writer->set( $new_invoice_token_cookie );

			throw new PaymentNotCompletedException( "[694039A0] Payment not found for invoice: {$invoice}" );
		} else {
			// 支払いが確認できた場合は販売履歴を更新し、リフレッシュトークンとアクセストークンを発行

			// 販売履歴を更新
			try {
				$chain = $this->chain_repository->get( $invoice->chainId() );
				if ( null === $chain ) {
					// chain テーブルに存在しないチェーンは販売履歴を更新できないためクロールのみスキップする
					$this->app_log_service->warn( "[D7A6B355] Chain with ID {$invoice->chainId()} does not exist. Skipping crawl for this chain." );
				} else {
					$this->app_contract_crawl_service->crawl( $chain );
				}
			} catch ( \Throwable $e ) {
				$this->app_log_service->error( $e );
				// 再スローはせずに処理を続行する
			}

			// 購入者ウォレットアドレスを取得
			$buyer_address = $invoice->buyerAddress();

			// リフレッシュトークン/アクセストークンを発行し、クッキーに保存
			$this->token_cookie_service->issueAndSetCookies( $buyer_address );

			// 請求書トークンは無効化してCookieから削除
			$this->invoice_token_service->revoke( $invoice_token_string, $now );
			$expired_invoice_token_cookie = $this->invoice_token_cookie_provider->getExpired();
			$this->cookie_writer->set( $expired_invoice_token_cookie );
		}

		// 非ログイン（ゲスト）のため user_id では区別できないので、実行者の wallet アドレスを message に入れる
		// 請求書トークンの値は記録しない
		$this->audit_log_service->info( 'auth_login_invoice', "Invoice token login succeeded. postId: {$invoice->postId()}, buyerAddress: {$invoice->buyerAddress()}" );
	}
}
