<?php
declare(strict_types=1);

namespace Baywall\Core\Application\Service;

use Baywall\Core\Application\Logging\AppLogService;
use Baywall\Core\Application\Port\SalesHistoryQueryService;
use Baywall\Core\Domain\Entity\Invoice;
use Baywall\Core\Domain\Repository\ChainRepository;
use Baywall\Core\Domain\Repository\InvoiceRepository;
use Baywall\Core\Domain\Repository\SearchCondition\InvoiceSearchCondition;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\PostId;

class PaymentVerifier {
	public function __construct(
		private readonly AppLogService $app_log_service,
		private readonly SalesHistoryQueryService $sales_history_query_service,
		private readonly InvoiceRepository $invoice_repository,
		private readonly ChainRepository $chain_repository,
		private readonly ConfirmationsService $confirmations_service,
		private readonly AppContractCrawlService $app_contract_crawl_service
	) {}

	public function isPaid( PostId $post_id, Address $buyer_address ): bool {
		if ( $this->sales_history_query_service->existsByPostIdAndBuyerAddress( $post_id, $buyer_address ) ) {
			return true;
		}
		$invoices = $this->invoice_repository->findBy( ( new InvoiceSearchCondition() )->setPostId( $post_id )->setBuyerAddress( $buyer_address ) );
		return $this->isConfirmedOnChain( $post_id, $buyer_address, $invoices );
	}

	/**
	 * @param Invoice[] $invoices
	 */
	private function isConfirmedOnChain( PostId $post_id, Address $buyer_address, array $invoices ): bool {
		/** @var int[] チェック対象のチェーンID一覧 */
		$invoice_chain_id_values = array_values( array_unique( array_map( fn ( Invoice $invoice ) => $invoice->chainId()->value(), $invoices ) ) );

		foreach ( $invoice_chain_id_values as $chain_id_value ) {
			$chain_id = ChainId::from( $chain_id_value );
			$chain    = $this->chain_repository->get( $chain_id );
			if ( null === $chain ) {
				$this->app_log_service->warn( "[9A308587] Chain with ID {$chain_id} does not exist. Skipping payment check for this chain." );
				continue; // chain テーブルに存在しないチェーンはスキップ
			}
			if ( ! $chain->connectable() ) {
				continue; // ブロックチェーンに接続できない場合はスキップ
			}

			if ( $this->confirmations_service->isConfirmed( $chain_id, $post_id, $buyer_address ) ) {
				try {
					// 販売履歴に存在しないが、ブロックチェーン上では支払が確認できている
					// という状態なので購入履歴を更新しておく
					$this->app_contract_crawl_service->crawl( $chain );
				} catch ( \Throwable $e ) {
					$this->app_log_service->error( $e );
					// 再スローはせずに処理を続行する
				}
				return true;
			}
		}
		return false;
	}
}
