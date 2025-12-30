<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Application\Repository\Erc4361NonceRepository;
use Cornix\Serendipity\Core\Application\Service\AccessTokenService;
use Cornix\Serendipity\Core\Application\Service\Erc4361Service;
use Cornix\Serendipity\Core\Application\Service\RefreshTokenCookieProvider;
use Cornix\Serendipity\Core\Application\Service\TransactionService;
use Cornix\Serendipity\Core\Application\Service\UnlockPaywallChecker;
use Cornix\Serendipity\Core\Domain\Exception\HttpStatus\BadRequestException;
use Cornix\Serendipity\Core\Domain\Exception\HttpStatus\PaymentRequiredException;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceRepository;
use Cornix\Serendipity\Core\Domain\Service\RefreshTokenService;
use Cornix\Serendipity\Core\Domain\ValueObject\Hex;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;
use Cornix\Serendipity\Core\Domain\ValueObject\Signature;
use Cornix\Serendipity\Core\Infrastructure\Cookie\CookieWriter;
use Cornix\Serendipity\Core\Infrastructure\Web3\Service\SignatureService;

/** ERC-4361の署名を検証してアクセストークン(+リフレッシュトークン)を発行するクラス */
class ResolveIssueAccessTokenByErc4361Signature {

	private TransactionService $transaction_service;
	private Erc4361Service $erc4361_service;
	private Erc4361NonceRepository $erc4361_nonce_repository;
	private InvoiceRepository $invoice_repository;
	private UnlockPaywallChecker $unlock_paywall_checker;
	private SignatureService $signature_service;
	private RefreshTokenService $refresh_token_service;
	private RefreshTokenCookieProvider $refresh_token_cookie_provider;
	private AccessTokenService $access_token_service;
	private CookieWriter $cookie_writer;

	public function __construct( TransactionService $transaction_service, Erc4361Service $erc4361_service, Erc4361NonceRepository $erc4361_nonce_repository, InvoiceRepository $invoice_repository, UnlockPaywallChecker $unlock_paywall_checker, SignatureService $signature_service, RefreshTokenService $refresh_token_service, RefreshTokenCookieProvider $refresh_token_cookie_provider, AccessTokenService $access_token_service, CookieWriter $cookie_writer ) {
		$this->transaction_service           = $transaction_service;
		$this->erc4361_service               = $erc4361_service;
		$this->erc4361_nonce_repository      = $erc4361_nonce_repository;
		$this->invoice_repository            = $invoice_repository;
		$this->unlock_paywall_checker        = $unlock_paywall_checker;
		$this->signature_service             = $signature_service;
		$this->refresh_token_service         = $refresh_token_service;
		$this->refresh_token_cookie_provider = $refresh_token_cookie_provider;
		$this->access_token_service          = $access_token_service;
		$this->cookie_writer                 = $cookie_writer;
	}

	public function handle( array $root_value, array $args ) {
		$invoice_id = InvoiceId::fromHex( Hex::from( $args['invoiceId'] ) );
		$signature  = Signature::from( $args['signature'] );

		return $this->transaction_service->transactional(
			function () use ( $invoice_id, $signature ) {
				if ( ! $this->unlock_paywall_checker->isPaywallUnlocked( $invoice_id ) ) {
					// 請求書IDのペイウォールが解除済みでない場合はエラー
					throw new PaymentRequiredException( "[6FF01B91] Unlock paywall transfer event not found for invoice: {$invoice_id}" );
				}

				// 購入者のアドレスから、保存済みのnonceを取得
				$customer_address = $this->invoice_repository->get( $invoice_id )->customerAddress();
				$stored_nonce     = $this->erc4361_nonce_repository->get( $customer_address );

				// 保存済みのnonceを使って署名用メッセージを再構築
				$message = $this->erc4361_service->createMessage( $invoice_id, $stored_nonce );
				// 再構築したメッセージと、受け取った署名からアドレスを計算
				$recovered_address = $this->signature_service->recoverAddress( $message, $signature );

				if ( ! $customer_address->equals( $recovered_address ) ) {
					// 署名の検証に失敗した場合はエラー
					throw new BadRequestException( "[27FA5840] ERC-4361 signature verification failed for invoice: {$invoice_id}" );
				}

				// リフレッシュトークンを発行し、クッキーに保存
				$refresh_token        = $this->refresh_token_service->issue( $customer_address );
				$refresh_token_cookie = $this->refresh_token_cookie_provider->get( $refresh_token );
				$this->cookie_writer->set( $refresh_token_cookie );

				// アクセストークンを発行
				$access_token = $this->access_token_service->issue( $customer_address );

				// アクセストークンはbodyで返す
				return array(
					'accessToken' => $access_token->value(),
				);
			}
		);
	}
}
