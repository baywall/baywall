<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Port\SalesHistoryQueryService;
use Baywall\Core\Application\Service\SalesHistorySearchConditionFactory;
use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\InvoiceId;
use Baywall\Core\Domain\ValueObject\TransactionHash;
use InvalidArgumentException;

class GetSalesHistoriesCount {
	public function __construct(
		private readonly UserAccessChecker $user_access_checker,
		private readonly SalesHistorySearchConditionFactory $search_condition_factory,
		private readonly SalesHistoryQueryService $sales_history_query_service
	) {}

	/**
	 * @throws InvalidArgumentException 負のタイムスタンプ
	 */
	public function handle( ?InvoiceId $invoice_id, ?ChainId $chain_id, ?TransactionHash $transaction_hash, ?int $date_from, ?int $date_to ): int {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		// フィルタ条件の構築は salesHistories と共有。limit / offset は count には無関係
		$condition = $this->search_condition_factory->buildFilter( $invoice_id, $chain_id, $transaction_hash, $date_from, $date_to );

		return $this->sales_history_query_service->count( $condition );
	}
}
