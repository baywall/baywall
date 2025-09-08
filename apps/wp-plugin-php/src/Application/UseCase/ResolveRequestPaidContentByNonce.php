<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Exception\ChainConnectionException;
use Cornix\Serendipity\Core\Application\Exception\InvoiceNonceMismatchException;
use Cornix\Serendipity\Core\Application\Exception\PurchaseValidationException;
use Cornix\Serendipity\Core\Application\Logging\AppLogger;
use Cornix\Serendipity\Core\Application\Service\AppContractCrawlService;
use Cornix\Serendipity\Core\Application\Service\RepositoryPurchaseChecker;
use Cornix\Serendipity\Core\Application\Service\TransactionService;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Domain\Entity\Invoice;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceRepository;
use Cornix\Serendipity\Core\Domain\Repository\PostRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceNonce;

class ResolveRequestPaidContentByNonce {

	// ここの定数は、GraphQLのエラーコードと一致させること
	private const ERROR_CODE_INVALID_NONCE           = 'INVALID_NONCE';
	private const ERROR_CODE_INVALID_CHAIN_ID        = 'INVALID_CHAIN_ID';
	private const ERROR_CODE_PAYWALL_LOCKED          = 'PAYWALL_LOCKED'; // TODO: 削除
	private const ERROR_CODE_TRANSACTION_UNCONFIRMED = 'TRANSACTION_UNCONFIRMED';

	private AppLogger $logger;
	private UserAccessChecker $user_access_checker;
	private InvoiceRepository $invoice_repository;
	private PostRepository $post_repository;
	private TransactionService $transaction_service;
	private RepositoryPurchaseChecker $repository_purchase_checker;
	private AppContractCrawlService $app_contract_crawl_service;

	public function __construct(
		AppLogger $logger,
		UserAccessChecker $user_access_checker,
		InvoiceRepository $invoice_repository,
		PostRepository $post_repository,
		TransactionService $transaction_service,
		RepositoryPurchaseChecker $repository_purchase_checker,
		AppContractCrawlService $app_contract_crawl_service
	) {
		$this->logger                      = $logger;
		$this->user_access_checker         = $user_access_checker;
		$this->invoice_repository          = $invoice_repository;
		$this->post_repository             = $post_repository;
		$this->transaction_service         = $transaction_service;
		$this->repository_purchase_checker = $repository_purchase_checker;
		$this->app_contract_crawl_service  = $app_contract_crawl_service;
	}

	public function handle( array $root_value, array $args ) {
		$invoice_nonce = InvoiceNonce::from( $args['nonce'] );
		$invoice_id    = InvoiceId::from( $args['invoiceId'] );
		$invoice       = $this->invoice_repository->get( $invoice_id );
		if ( $invoice === null ) {
			throw new \InvalidArgumentException( "[B8057163] Invoice not found: {$invoice_id}" );
		}

		// 投稿を閲覧できる権限があることをチェック
		$this->user_access_checker->checkCanViewPost( $invoice->postId()->value() );

		try {
			$this->transaction_service->beginTransaction();

			// 事前チェック
			$this->checkNonce( $invoice, $invoice_nonce );  // nonceの確認（期待するnonceでない場合は例外が発生）
			$this->checkIsPurchased( $invoice );    // 購入済みかどうかを確認（購入が確認できない場合は例外が発生）

			// nonceの振り直し
			$invoice->setNonce( InvoiceNonce::generate() );
			$this->invoice_repository->save( $invoice );

			// 有料部分の取得
			$paid_content = $this->post_repository->get( $invoice->postId() )->paidContent();

			$this->transaction_service->commit();
			return array(
				'content'   => $paid_content->value(),
				'newNonce'  => $invoice->nonce()->value(),
				'errorCode' => null,
			);
		} catch ( \Throwable $e ) {
			$this->logger->error( $e );

			// 例外が発生した場合はエラーコードを設定
			if ( $e instanceof InvoiceNonceMismatchException ) {
				// invoice に紐づく nonce が期待する値と一致しなかった場合
				$error_code = self::ERROR_CODE_INVALID_NONCE;
			} elseif ( $e instanceof ChainConnectionException ) {
				// チェーンへの接続に失敗した場合
				$error_code = self::ERROR_CODE_INVALID_CHAIN_ID;
			} elseif ( $e instanceof PurchaseValidationException ) {
				// 購入が確認できなかった場合
				$error_code = self::ERROR_CODE_TRANSACTION_UNCONFIRMED;
			} else {
				throw $e; // その他の例外はそのまま投げる
			}
			return array(
				'content'   => null,
				'newNonce'  => null,
				'errorCode' => $error_code,
			);
		}
	}

	/**
	 * 受け取ったInvoiceのnonceがリポジトリに保存されているnonceと一致することを確認し、一致しない場合は例外をスローします
	 *
	 * @param Invoice      $invoice
	 * @param InvoiceNonce $invoice_nonce
	 * @throws InvoiceNonceMismatchException
	 */
	private function checkNonce( Invoice $invoice, InvoiceNonce $invoice_nonce ): void {
		$repository_invoice_nonce = $invoice->nonce(); // リポジトリに保存されているnonceを取得
		if ( $repository_invoice_nonce === null || ! $repository_invoice_nonce->equals( $invoice_nonce ) ) {
			// 期待するnonceでない場合は例外をスロー
			throw new InvoiceNonceMismatchException( "[FA976383] Invoice nonce mismatch for ID: {$invoice->id()}, expected: {$repository_invoice_nonce}, got: {$invoice_nonce}" );
		}
	}

	/**
	 * 購入済みかどうかを確認し、購入が確認できない場合は例外をスローします
	 *
	 * @param Invoice $invoice
	 * @throws PurchaseValidationException
	 */
	private function checkIsPurchased( Invoice $invoice ): void {

		$invoice_id = $invoice->id();

		// 購入済みかどうかをリポジトリで確認
		$is_purchased = $this->repository_purchase_checker->isPurchased( $invoice_id );
		if ( $is_purchased ) {
			// 購入済みの場合は例外を投げずに処理を終了
			return;
		} else {
			// 購入が確認できなかった場合はコントラクトのイベントをクロール
			$this->app_contract_crawl_service->crawl( $invoice->chainId() );

			// 再度購入済みかどうかを確認
			if ( $this->repository_purchase_checker->isPurchased( $invoice_id ) ) {
				// 購入済みの場合は例外を投げずに処理を終了
				return;
			} else {
				// 購入が確認できなかった場合は例外をスロー
				throw new PurchaseValidationException( "[3FC07907] Purchase validation failed for invoice ID: {$invoice_id}" );
			}
		}
	}
}
