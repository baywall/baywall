<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Service\AccessTokenCookieProvider;
use Cornix\Serendipity\Core\Application\Service\AccessTokenService;
use Cornix\Serendipity\Core\Application\Service\AppContractCrawlService;
use Cornix\Serendipity\Core\Application\Service\InvoiceTokenCookieProvider;
use Cornix\Serendipity\Core\Application\Service\RefreshTokenCookieProvider;
use Cornix\Serendipity\Core\Domain\Exception\HttpStatus\PaymentRequiredException;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceRepository;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceTokenRepository;
use Cornix\Serendipity\Core\Domain\Service\InvoiceTokenService;
use Cornix\Serendipity\Core\Domain\Service\RefreshTokenService;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceTokenString;
use Cornix\Serendipity\Core\Infrastructure\Cookie\CookieWriter;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\UnlockPaywallTransferEventRepository;
use InvalidArgumentException;

/**
 * 請求書トークンとアクセストークン(+リフレッシュトークン)の引き換えを行うクラス
 *
 * ここでは、トランザクションがブロックに存在するかどうかで請求書トークンの持ち主の確定を行っています。
 * サーバーが指定した待機ブロック数については考慮していないことに注意してください。
 * ※ サーバー指定の待機ブロック数は有料記事取得時に判定します
 */
class IssueAccessTokenByInvoiceToken {

	private InvoiceTokenRepository $invoice_token_repository;
	private InvoiceRepository $invoice_repository;
	private RefreshTokenService $refresh_token_service;
	private RefreshTokenCookieProvider $refresh_token_cookie_provider;
	private AccessTokenCookieProvider $access_token_cookie_provider;
	private AccessTokenService $access_token_service;
	private CookieWriter $cookie_writer;
	private AppContractCrawlService $app_contract_crawl_service;
	private UnlockPaywallTransferEventRepository $unlock_paywall_transfer_event_repository;
	private InvoiceTokenService $invoice_token_service;
	private InvoiceTokenCookieProvider $invoice_token_cookie_provider;

	public function __construct( InvoiceTokenRepository $invoice_token_repository, InvoiceRepository $invoice_repository, RefreshTokenService $refresh_token_service, RefreshTokenCookieProvider $refresh_token_cookie_provider, AccessTokenCookieProvider $access_token_cookie_provider, AccessTokenService $access_token_service, CookieWriter $cookie_writer, AppContractCrawlService $app_contract_crawl_service, UnlockPaywallTransferEventRepository $unlock_paywall_transfer_event_repository, InvoiceTokenService $invoice_token_service, InvoiceTokenCookieProvider $invoice_token_cookie_provider ) {
		$this->invoice_token_repository                 = $invoice_token_repository;
		$this->invoice_repository                       = $invoice_repository;
		$this->refresh_token_service                    = $refresh_token_service;
		$this->refresh_token_cookie_provider            = $refresh_token_cookie_provider;
		$this->access_token_cookie_provider             = $access_token_cookie_provider;
		$this->access_token_service                     = $access_token_service;
		$this->cookie_writer                            = $cookie_writer;
		$this->app_contract_crawl_service               = $app_contract_crawl_service;
		$this->unlock_paywall_transfer_event_repository = $unlock_paywall_transfer_event_repository;
		$this->invoice_token_service                    = $invoice_token_service;
		$this->invoice_token_cookie_provider            = $invoice_token_cookie_provider;
	}

	public function handle( string $invoice_token_string_value ): void {

		$invoice_token_string = InvoiceTokenString::from( $invoice_token_string_value );

		$invoice_token = $this->invoice_token_repository->get( $invoice_token_string );
		if ( $invoice_token === null ) {
			throw new InvalidArgumentException( "[BCD15F61] Invalid invoice token: {$invoice_token_string_value}" );
		}

		// 請求書のチェーンに対してAppコントラクトイベントをクロール
		$invoice = $this->invoice_repository->get( $invoice_token->invoiceId() );
		$this->app_contract_crawl_service->crawl( $invoice->chainId() );

		// 購入時のトランザクションが含まれるブロック番号を取得
		$payment_block_number = $this->unlock_paywall_transfer_event_repository->getBlockNumber( $invoice_token->invoiceId() );
		if ( $payment_block_number === null ) {
			// （まだ）支払いが確認できない場合は請求書トークンのローテーションを行い、例外をスロー
			// ※この後ブロックに取り込まれる可能性もあるのでCookieの無効化は行わない

			// 請求書トークンのローテーション(DB更新+Cookie書き込み)
			$new_invoice_token        = $this->invoice_token_service->rotation( $invoice_token_string );
			$new_invoice_token_cookie = $this->invoice_token_cookie_provider->get( $new_invoice_token );
			$this->cookie_writer->set( $new_invoice_token_cookie );

			throw new PaymentRequiredException( "[694039A0] Payment not found for invoice: {$invoice}" );
		} else {
			// 支払いが確認できた場合はリフレッシュトークンとアクセストークンを発行
			// リフレッシュトークン及びアクセストークンはCookieに保存

			// 購入者ウォレットアドレスを取得
			$customer_address = $invoice->customerAddress();

			// リフレッシュトークンを発行し、クッキーに保存
			$refresh_token        = $this->refresh_token_service->issue( $customer_address );
			$refresh_token_cookie = $this->refresh_token_cookie_provider->get( $refresh_token );
			$this->cookie_writer->set( $refresh_token_cookie );

			// アクセストークンを発行
			$access_token        = $this->access_token_service->issue( $customer_address );
			$access_token_cookie = $this->access_token_cookie_provider->get( $access_token );
			$this->cookie_writer->set( $access_token_cookie );

			// 請求書トークンは無効化してCookieから削除
			$this->invoice_token_service->revoke( $invoice_token_string );
			$expired_invoice_token_cookie = $this->invoice_token_cookie_provider->getExpired();
			$this->cookie_writer->set( $expired_invoice_token_cookie );
		}
	}
}
