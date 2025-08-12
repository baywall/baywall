<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Exception\InvoiceNonceMismatchException;
use Cornix\Serendipity\Core\Application\Exception\InvoiceNotFoundException;
use Cornix\Serendipity\Core\Application\Exception\PurchaseValidationException;
use Cornix\Serendipity\Core\Application\Service\AppContractCrawlService;
use Cornix\Serendipity\Core\Application\Service\RepositoryPurchaseChecker;
use Cornix\Serendipity\Core\Application\Service\TransactionService;
use Cornix\Serendipity\Core\Domain\Entity\Invoice;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceRepository;
use Cornix\Serendipity\Core\Domain\Repository\PostRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceNonce;

class GetPaidContentByNonce {

	private RepositoryPurchaseChecker $repository_purchase_checker;
	private InvoiceRepository $invoice_repository;
	private PostRepository $post_repository;
	private AppContractCrawlService $app_contract_crawl_service;
	private TransactionService $transaction_service;

	public function __construct(
		RepositoryPurchaseChecker $repository_purchase_checker,
		InvoiceRepository $invoice_repository,
		PostRepository $post_repository,
		AppContractCrawlService $app_contract_crawl_service,
		TransactionService $transaction_service
	) {
		$this->repository_purchase_checker = $repository_purchase_checker;
		$this->invoice_repository          = $invoice_repository;
		$this->post_repository             = $post_repository;
		$this->app_contract_crawl_service  = $app_contract_crawl_service;
		$this->transaction_service         = $transaction_service;
	}

	public function handle( string $invoice_id_value, string $invoice_nonce_value ): GetPaidContentByNonceResultDto {
		$invoice_id    = InvoiceId::from( $invoice_id_value );
		$invoice_nonce = InvoiceNonce::from( $invoice_nonce_value );

		try {
			$this->transaction_service->beginTransaction();
			$result = $this->execute( $invoice_id, $invoice_nonce );
			$this->transaction_service->commit();
			return $result;
		} catch ( \Throwable $e ) {
			// 例外が発生した場合はトランザクションをロールバック
			$this->transaction_service->rollback();
			throw $e;
		}
	}

	private function execute( InvoiceId $invoice_id, InvoiceNonce $invoice_nonce ): GetPaidContentByNonceResultDto {

		$invoice                  = $this->getInvoice( $invoice_id );
		$repository_invoice_nonce = $invoice->nonce();
		if ( $repository_invoice_nonce === null || ! $repository_invoice_nonce->equals( $invoice_nonce ) ) {
			// 期待するnonceでない場合は例外をスロー
			throw new InvoiceNonceMismatchException( "[FA976383] Invoice nonce mismatch for ID: {$invoice_id}, expected: {$repository_invoice_nonce}, got: {$invoice_nonce}" );
		}

		// 購入済みかどうかをリポジトリで確認
		$is_purchased = $this->repository_purchase_checker->isPurchased( $invoice_id );
		if ( $is_purchased ) {
			// 購入済みの場合は結果を返す
			return $this->getResult( $invoice );
		} else {
			// 購入が確認できなかった場合はコントラクトのイベントをクロール
			$this->app_contract_crawl_service->crawl( $invoice->chainId() );

			// 再度購入済みかどうかを確認
			if ( $this->repository_purchase_checker->isPurchased( $invoice_id ) ) {
				// 購入済みの場合は結果を返す
				return $this->getResult( $invoice );
			} else {
				// 購入が確認できなかった場合は例外をスロー
				throw new PurchaseValidationException( "[3FC07907] Purchase validation failed for invoice ID: {$invoice_id}" );
			}
		}
	}

	private function getResult( Invoice $invoice ): GetPaidContentByNonceResultDto {
		// 有料部分の取得
		$post_id      = $invoice->postId();
		$paid_content = $this->post_repository->get( $post_id )->paidContent();
		if ( $paid_content === null ) {
			throw new \RuntimeException( "[2CA7AF7F] Paid content not found for post ID: {$post_id}" );
		}

		// nonceの振り直し
		$invoice->setNonce( InvoiceNonce::generate() );
		$this->invoice_repository->save( $invoice );

		return new GetPaidContentByNonceResultDto(
			$paid_content->value(),
			$invoice->nonce()->value()
		);
	}

	private function getInvoice( InvoiceId $invoice_id ): Invoice {
		$invoice = $this->invoice_repository->get( $invoice_id );
		if ( $invoice === null ) {
			// リポジトリから invoice 情報を取得できなかった場合は例外をスロー
			throw new InvoiceNotFoundException( "[BF6F4FC2] Invoice not found for ID: {$invoice_id}" );
		}
		return $invoice;
	}
}

class GetPaidContentByNonceResultDto {
	public string $paid_content;
	public string $new_nonce;

	public function __construct( string $paid_content_value, string $new_nonce_value ) {
		$this->paid_content = $paid_content_value;
		$this->new_nonce    = $new_nonce_value;
	}
}
