<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Logging\AppLogger;
use Baywall\Core\Application\Service\AccessTokenCookieProvider;
use Baywall\Core\Application\Service\AccessTokenService;
use Baywall\Core\Application\Service\AppContractCrawlService;
use Baywall\Core\Application\Service\ConfirmationsService;
use Baywall\Core\Application\Service\InvoiceTokenCookieProvider;
use Baywall\Core\Application\Service\RefreshTokenCookieProvider;
use Baywall\Core\Domain\Exception\HttpStatus\PaymentRequiredException;
use Baywall\Core\Domain\Service\InvoiceService;
use Baywall\Core\Domain\Service\InvoiceTokenService;
use Baywall\Core\Domain\Service\RefreshTokenService;
use Baywall\Core\Domain\ValueObject\InvoiceTokenString;
use Baywall\Core\Infrastructure\Cookie\CookieWriter;

/**
 * 請求書トークンとアクセストークン(+リフレッシュトークン)の引き換えを行うクラス
 *
 * ここでは、トランザクションがブロックに存在するかどうかで請求書トークンの持ち主の確定を行っています。
 * サーバーが指定した待機ブロック数については考慮していないことに注意してください。
 * ※ サーバー指定の待機ブロック数は有料記事取得時に判定します
 */
class IssueAccessTokenByInvoiceToken {


	public function __construct(
		private readonly AppLogger $logger,
		private readonly RefreshTokenService $refresh_token_service,
		private readonly RefreshTokenCookieProvider $refresh_token_cookie_provider,
		private readonly AccessTokenCookieProvider $access_token_cookie_provider,
		private readonly AccessTokenService $access_token_service,
		private readonly CookieWriter $cookie_writer,
		private readonly InvoiceTokenService $invoice_token_service,
		private readonly InvoiceTokenCookieProvider $invoice_token_cookie_provider,
		private readonly InvoiceService $invoice_service,
		private readonly ConfirmationsService $confirmations_service,
		private readonly AppContractCrawlService $app_contract_crawl_service
	) {}

	public function handle( string $invoice_token_string_value ): void {
		$invoice_token_string = InvoiceTokenString::from( $invoice_token_string_value );
		// 請求書トークンの文字列から発行した請求書を取得
		$invoice = $this->invoice_service->getByInvoiceTokenString( $invoice_token_string );

		// 支払いの確認が取れたかどうかを取得
		$is_confirmed = $this->confirmations_service->isConfirmed( $invoice->chainId(), $invoice->postId(), $invoice->buyerAddress() );

		if ( $is_confirmed === false ) {
			// （まだ）支払いが確認できない場合は請求書トークンのローテーションを行い、例外をスロー
			// ※この後ブロックに取り込まれる可能性もあるのでCookieの無効化は行わない

			// 請求書トークンのローテーション(DB更新+Cookie書き込み)
			$new_invoice_token        = $this->invoice_token_service->rotation( $invoice_token_string );
			$new_invoice_token_cookie = $this->invoice_token_cookie_provider->get( $new_invoice_token );
			$this->cookie_writer->set( $new_invoice_token_cookie );

			throw new PaymentRequiredException( "[694039A0] Payment not found for invoice: {$invoice}" );
		} else {
			// 支払いが確認できた場合は販売履歴を更新し、リフレッシュトークンとアクセストークンを発行

			// 販売履歴を更新
			try {
				$this->app_contract_crawl_service->crawl( $invoice->chainId() );
			} catch ( \Throwable $e ) {
				$this->logger->error( $e );
				// 再スローはせずに処理を続行する
			}

			// 購入者ウォレットアドレスを取得
			$buyer_address = $invoice->buyerAddress();

			// リフレッシュトークンを発行し、クッキーに保存
			$refresh_token        = $this->refresh_token_service->issue( $buyer_address );
			$refresh_token_cookie = $this->refresh_token_cookie_provider->get( $refresh_token );
			$this->cookie_writer->set( $refresh_token_cookie );

			// アクセストークンを発行
			$access_token        = $this->access_token_service->issue( $buyer_address );
			$access_token_cookie = $this->access_token_cookie_provider->get( $access_token );
			$this->cookie_writer->set( $access_token_cookie );

			// 請求書トークンは無効化してCookieから削除
			$this->invoice_token_service->revoke( $invoice_token_string );
			$expired_invoice_token_cookie = $this->invoice_token_cookie_provider->getExpired();
			$this->cookie_writer->set( $expired_invoice_token_cookie );
		}
	}
}
