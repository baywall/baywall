<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Dto\SalesHistoryDto;
use Baywall\Core\Application\Port\SalesHistoryQueryService;
use Baywall\Core\Application\Service\SalesHistorySearchConditionFactory;
use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\InvoiceId;
use Baywall\Core\Domain\ValueObject\TransactionHash;
use InvalidArgumentException;

class GetSalesHistories {
	public function __construct(
		private readonly UserAccessChecker $user_access_checker,
		private readonly SalesHistorySearchConditionFactory $search_condition_factory,
		private readonly SalesHistoryQueryService $sales_history_query_service
	) {}

	/**
	 * @throws InvalidArgumentException 負のタイムスタンプ、負の limit / offset
	 * @return SalesHistoryDto[]
	 */
	public function handle( ?InvoiceId $invoice_id, ?ChainId $chain_id, ?TransactionHash $transaction_hash, ?int $date_from, ?int $date_to, ?int $limit, ?int $offset ): array {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		// 検索条件の構築
		$condition = $this->search_condition_factory->buildFilter( $invoice_id, $chain_id, $transaction_hash, $date_from, $date_to );
		$this->search_condition_factory->applyPaging( $condition, $limit, $offset );

		return $this->sales_history_query_service->find( $condition );
	}
}
