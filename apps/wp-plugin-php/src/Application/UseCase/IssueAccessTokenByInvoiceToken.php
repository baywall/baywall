<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Service\AccessTokenService;
use Cornix\Serendipity\Core\Application\Service\AppContractCrawlService;
use Cornix\Serendipity\Core\Application\Service\RefreshTokenCookieProvider;
use Cornix\Serendipity\Core\Application\Service\TransactionService;
use Cornix\Serendipity\Core\Domain\Exception\InsufficientConfirmationsException;
use Cornix\Serendipity\Core\Domain\Exception\InvalidInvoiceTokenException;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceRepository;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceTokenRepository;
use Cornix\Serendipity\Core\Domain\Service\PaymentStatusService;
use Cornix\Serendipity\Core\Domain\Service\RefreshTokenService;
use Cornix\Serendipity\Core\Domain\ValueObject\Hex;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceTokenString;
use Cornix\Serendipity\Core\Infrastructure\Cookie\CookieWriter;

/** 請求書トークンとアクセストークン(+リフレッシュトークン)の引き換えを行うクラス */
class IssueAccessTokenByInvoiceToken {

	private TransactionService $transaction_service;
	private InvoiceTokenRepository $invoice_token_repository;
	private InvoiceRepository $invoice_repository;
	private RefreshTokenService $refresh_token_service;
	private RefreshTokenCookieProvider $refresh_token_cookie_provider;
	private AccessTokenService $access_token_service;
	private CookieWriter $cookie_writer;
	private PaymentStatusService $payment_status_service;
	private AppContractCrawlService $app_contract_crawl_service;

	public function __construct( TransactionService $transaction_service, InvoiceTokenRepository $invoice_token_repository, InvoiceRepository $invoice_repository, RefreshTokenService $refresh_token_service, RefreshTokenCookieProvider $refresh_token_cookie_provider, AccessTokenService $access_token_service, CookieWriter $cookie_writer, PaymentStatusService $payment_status_service, AppContractCrawlService $app_contract_crawl_service ) {
		$this->transaction_service           = $transaction_service;
		$this->invoice_token_repository      = $invoice_token_repository;
		$this->invoice_repository            = $invoice_repository;
		$this->refresh_token_service         = $refresh_token_service;
		$this->refresh_token_cookie_provider = $refresh_token_cookie_provider;
		$this->access_token_service          = $access_token_service;
		$this->cookie_writer                 = $cookie_writer;
		$this->payment_status_service        = $payment_status_service;
		$this->app_contract_crawl_service    = $app_contract_crawl_service;
	}

	public function handle( string $invoice_id_hex_value, string $invoice_token_string_value ) {
		return $this->transaction_service->transactional(
			function () use ( $invoice_id_hex_value, $invoice_token_string_value ) {
				$invoice_id           = InvoiceId::fromHex( Hex::from( $invoice_id_hex_value ) );
				$invoice_token_string = InvoiceTokenString::from( $invoice_token_string_value );

				$invoice_token = $this->invoice_token_repository->get( $invoice_id, $invoice_token_string );
				if ( $invoice_token === null ) {
					throw new InvalidInvoiceTokenException( "[BCD15F61] id: {$invoice_id_hex_value}, token: {$invoice_token_string_value}" );
				}

				// 請求書のチェーンに対してAppコントラクトイベントをクロール
				$invoice = $this->invoice_repository->get( $invoice_id );
				$this->app_contract_crawl_service->crawl( $invoice->chainId() );

				// 請求書の支払いが完了していることを確認
				$is_confirmed = $this->payment_status_service->isConfirmed( $invoice_id );
				if ( $is_confirmed !== true ) {
					throw new InsufficientConfirmationsException( "[FAB1E17E] id: {$invoice_id_hex_value}" );
				}

				// この時点で、invoiceを発行したユーザーであることが確認できた判定
				$consumer_address = $invoice->consumerAddress();

				// アクセストークンを発行
				$access_token = $this->access_token_service->issue( $consumer_address );
				// リフレッシュトークンを発行
				$refresh_token = $this->refresh_token_service->issue( $consumer_address );

				// リフレッシュトークンはクッキーに保存
				$refresh_token_cookie = $this->refresh_token_cookie_provider->get( $refresh_token );
				$this->cookie_writer->set( $refresh_token_cookie );

				// アクセストークンはレスポンスボディで返却
				return array(
					'access_token' => $access_token->value(),
				);
			}
		);
	}
}
