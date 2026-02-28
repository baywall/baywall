<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Application\Repository\Erc4361NonceRepository;
use Cornix\Serendipity\Core\Application\Service\Erc4361NonceProvider;
use Cornix\Serendipity\Core\Application\Service\Erc4361Service;
use Cornix\Serendipity\Core\Application\Service\TransactionService;
use Cornix\Serendipity\Core\Application\Service\UnlockPaywallChecker;
use Cornix\Serendipity\Core\Domain\Exception\HttpStatus\BadRequestException;
use Cornix\Serendipity\Core\Domain\Exception\HttpStatus\PaymentRequiredException;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\Hex;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;

class ResolveIssueErc4361Message {

	private TransactionService $transaction_service;
	private Erc4361NonceRepository $erc4361_nonce_repository;
	private Erc4361NonceProvider $erc4361_nonce_provider;
	private Erc4361Service $erc4361_service;
	private InvoiceRepository $invoice_repository;
	private UnlockPaywallChecker $unlock_paywall_checker;

	public function __construct( TransactionService $transaction_service, Erc4361NonceRepository $erc4361_nonce_repository, Erc4361NonceProvider $erc4361_nonce_provider, Erc4361Service $erc4361_service, InvoiceRepository $invoice_repository, UnlockPaywallChecker $unlock_paywall_checker ) {
		$this->transaction_service      = $transaction_service;
		$this->erc4361_nonce_repository = $erc4361_nonce_repository;
		$this->erc4361_nonce_provider   = $erc4361_nonce_provider;
		$this->erc4361_service          = $erc4361_service;
		$this->invoice_repository       = $invoice_repository;
		$this->unlock_paywall_checker   = $unlock_paywall_checker;
	}

	public function handle( array $root_value, array $args ) {
		$invoice_id = InvoiceId::fromHex( Hex::from( $args['invoiceId'] ) );

		return $this->transaction_service->transactional(
			function () use ( $invoice_id ) {
				if ( ! $this->unlock_paywall_checker->isPaywallUnlocked( $invoice_id ) ) {
					// 請求書IDのペイウォールが解除済みでない場合はエラー
					throw new PaymentRequiredException( "[20DC57FF] Unlock paywall transfer event not found for invoice: {$invoice_id}" );
				}

				$invoice = $this->invoice_repository->get( $invoice_id );
				if ( $invoice === null ) {
					throw new BadRequestException( "[4244B369] Invoice not found: {$invoice_id}" );
				}
				// 購入者アドレスを取得
				$customer_address = $invoice->customerAddress();

				// nonceを生成し、購入者アドレスと紐づけて保存
				$nonce = $this->erc4361_nonce_provider->generate();
				$this->erc4361_nonce_repository->save( $customer_address, $nonce );

				// 署名用メッセージを生成
				$erc4361_message = $this->erc4361_service->createMessage( $invoice_id, $nonce );

				return array(
					'message' => $erc4361_message->value(),
				);
			}
		);
	}
}
