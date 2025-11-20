<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Service\AccessTokenService;
use Cornix\Serendipity\Core\Application\Service\AppContractCrawlService;
use Cornix\Serendipity\Core\Application\Service\BlockNumberProvider;
use Cornix\Serendipity\Core\Application\Service\RefreshTokenCookieProvider;
use Cornix\Serendipity\Core\Application\Service\TransactionService;
use Cornix\Serendipity\Core\Application\ValueObject\AccessToken;
use Cornix\Serendipity\Core\Domain\Exception\HttpStatus\UnauthorizedException;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceRepository;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceTokenRepository;
use Cornix\Serendipity\Core\Domain\Service\RefreshTokenService;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockHeight;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockTag;
use Cornix\Serendipity\Core\Domain\ValueObject\Hex;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceTokenString;
use Cornix\Serendipity\Core\Infrastructure\Cookie\CookieWriter;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Database\Repository\UnlockPaywallTransferEventRepository;
use RuntimeException;

/** 請求書トークンとアクセストークン(+リフレッシュトークン)の引き換えを行うクラス */
class IssueAccessTokenByInvoiceToken {

	private TransactionService $transaction_service;
	private InvoiceTokenRepository $invoice_token_repository;
	private InvoiceRepository $invoice_repository;
	private RefreshTokenService $refresh_token_service;
	private RefreshTokenCookieProvider $refresh_token_cookie_provider;
	private AccessTokenService $access_token_service;
	private CookieWriter $cookie_writer;
	private AppContractCrawlService $app_contract_crawl_service;
	private UnlockPaywallTransferEventRepository $unlock_paywall_transfer_event_repository;
	private ChainRepository $chain_repository;
	private BlockNumberProvider $block_number_provider;

	public function __construct( TransactionService $transaction_service, InvoiceTokenRepository $invoice_token_repository, InvoiceRepository $invoice_repository, RefreshTokenService $refresh_token_service, RefreshTokenCookieProvider $refresh_token_cookie_provider, AccessTokenService $access_token_service, CookieWriter $cookie_writer, AppContractCrawlService $app_contract_crawl_service, UnlockPaywallTransferEventRepository $unlock_paywall_transfer_event_repository, ChainRepository $chain_repository, BlockNumberProvider $block_number_provider ) {
		$this->transaction_service                      = $transaction_service;
		$this->invoice_token_repository                 = $invoice_token_repository;
		$this->invoice_repository                       = $invoice_repository;
		$this->refresh_token_service                    = $refresh_token_service;
		$this->refresh_token_cookie_provider            = $refresh_token_cookie_provider;
		$this->access_token_service                     = $access_token_service;
		$this->cookie_writer                            = $cookie_writer;
		$this->app_contract_crawl_service               = $app_contract_crawl_service;
		$this->unlock_paywall_transfer_event_repository = $unlock_paywall_transfer_event_repository;
		$this->chain_repository                         = $chain_repository;
		$this->block_number_provider                    = $block_number_provider;
	}

	public function handle( string $invoice_id_hex_value, string $invoice_token_string_value ): array {
		return $this->transaction_service->transactional(
			function () use ( $invoice_id_hex_value, $invoice_token_string_value ) {
				$invoice_id           = InvoiceId::fromHex( Hex::from( $invoice_id_hex_value ) );
				$invoice_token_string = InvoiceTokenString::from( $invoice_token_string_value );

				$invoice_token = $this->invoice_token_repository->get( $invoice_id, $invoice_token_string );
				if ( $invoice_token === null ) {
					throw new UnauthorizedException( "[BCD15F61] id: {$invoice_id_hex_value}, token: {$invoice_token_string_value}" );
				}

				// TODO: 請求書トークンのローテーション

				// 請求書のチェーンに対してAppコントラクトイベントをクロール
				$invoice = $this->invoice_repository->get( $invoice_id );
				$this->app_contract_crawl_service->crawl( $invoice->chainId() );

				// チェーンのconfirmationsを取得
				$chain_confirmations = $this->chain_repository->get( $invoice->chainId() )->confirmations();
				if ( ! is_int( $chain_confirmations->value() ) ) {
					throw new RuntimeException( "[A6669AE2] Not supported chain for confirmations type. {$$chain_confirmations}" );
				}
				$required_confirmations = BlockHeight::from( $chain_confirmations->value() );

				// 購入時のトランザクションが含まれるブロック番号を取得
				$payment_block_number = $this->unlock_paywall_transfer_event_repository->getBlockNumber( $invoice_id );

				if ( $payment_block_number === null ) {
					// 支払いが確認できない場合、確認ブロック数0でレスポンスを返す
					return ( new IssueAccessTokenByInvoiceTokenResult(
						null,
						$required_confirmations,
						BlockHeight::from( 0 )
					) )->toArray();
				}

				// 現在のブロック番号を取得
				$current_block_number = $this->block_number_provider->getByChainId( $invoice->chainId(), BlockTag::latest() );

				// 確認ブロック数を計算
				$confirmed_block_height_value = $current_block_number->int() - $payment_block_number->int() + 1;
				// 負の値にならないように念のため補正(購入時のブロック番号が取得できているので1以上)
				$confirmed_block_height = BlockHeight::from( max( 1, $confirmed_block_height_value ) );

				// 支払いが完了しているかどうかを判定
				$is_confirmed = $required_confirmations->value() <= $confirmed_block_height->value();

				if ( $is_confirmed ) {
					$consumer_address = $invoice->consumerAddress();

					// アクセストークンを発行
					$access_token = $this->access_token_service->issue( $consumer_address );
					// リフレッシュトークンを発行
					$refresh_token = $this->refresh_token_service->issue( $consumer_address );

					// リフレッシュトークンはクッキーに保存
					$refresh_token_cookie = $this->refresh_token_cookie_provider->get( $refresh_token );
					$this->cookie_writer->set( $refresh_token_cookie );

					// TODO: 請求書トークンの無効化
					// - 請求書トークンテーブルから削除
					// - Cookieの上書き

					return ( new IssueAccessTokenByInvoiceTokenResult(
						$access_token,
						$required_confirmations,
						$confirmed_block_height
					) )->toArray();
				} else {
					return ( new IssueAccessTokenByInvoiceTokenResult(
						null,
						$required_confirmations,
						$confirmed_block_height
					) )->toArray();
				}
			}
		);
	}
}

/** @internal */
class IssueAccessTokenByInvoiceTokenResult {

	/** クライアントに渡すアクセストークン */
	private ?AccessToken $access_token;

	/** サーバーが購入済みと判定する確認ブロック数 */
	private BlockHeight $required;

	/** 現在の確認ブロック数 */
	private BlockHeight $confirmed;

	public function __construct( ?AccessToken $access_token, BlockHeight $required, BlockHeight $confirmed ) {
		$this->access_token = $access_token;
		$this->required     = $required;
		$this->confirmed    = $confirmed;
	}

	public function toArray(): array {
		return array(
			'access_token'  => $this->access_token ? $this->access_token->value() : null,
			'confirmations' => array(
				'required'  => $this->required->value(),
				'confirmed' => $this->confirmed->value(),
			),
		);
	}

	public function accessToken(): ?AccessToken {
		return $this->access_token;
	}
}
