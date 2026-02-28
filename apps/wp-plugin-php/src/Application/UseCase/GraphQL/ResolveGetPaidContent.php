<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Application\Service\AppContractCrawlService;
use Cornix\Serendipity\Core\Application\Service\TransactionService;
use Cornix\Serendipity\Core\Application\UseCase\GetPaidContent;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\Hex;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceId;

class ResolveGetPaidContent {

	private GetPaidContent $get_paid_content;
	private TransactionService $transaction_service;
	private InvoiceRepository $invoice_repository;
	private AppContractCrawlService $app_contract_crawl_service;

	public function __construct(
		GetPaidContent $get_paid_content,
		TransactionService $transaction_service,
		InvoiceRepository $invoice_repository,
		AppContractCrawlService $app_contract_crawl_service
	) {
		$this->get_paid_content           = $get_paid_content;
		$this->transaction_service        = $transaction_service;
		$this->invoice_repository         = $invoice_repository;
		$this->app_contract_crawl_service = $app_contract_crawl_service;
	}

	public function handle( array $root_value, array $args ) {

		$invoice_id = InvoiceId::fromHex( Hex::from( $args['invoiceId'] ) );

		$this->transaction_service->transactional(
			function () use ( $invoice_id ) {
				// 請求書のチェーンに対してAppコントラクトイベントをクロール
				$invoice = $this->invoice_repository->get( $invoice_id );
				$this->app_contract_crawl_service->crawl( $invoice->chainId() );
			}
		);

		// 有料コンテンツを取得
		$paid_content_value = $this->get_paid_content->handle( $invoice_id->hex() );
		return array(
			// TODO: WordPressのフィルタを使っているのでApplication層に置くのは不適切。
			// Presentation層に移動するか、Infrastructure層に移動する
			'paidContent' => apply_filters( 'the_content', $paid_content_value ),
		);
	}
}
