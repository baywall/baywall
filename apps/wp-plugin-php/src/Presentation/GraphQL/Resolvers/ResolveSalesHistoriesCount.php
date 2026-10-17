<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\Service\SalesHistoryQueryService;
use Baywall\Core\Application\Service\UserAccessChecker;

class ResolveSalesHistoriesCount {

	public function __construct(
		private readonly UserAccessChecker $user_access_checker,
		private readonly SalesHistoryQueryService $sales_history_service
	) {}

	public function handle( array $root_value, array $args ): int {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		// フィルタ条件の構築は salesHistories と共有。limit / offset は count には無関係
		$condition = ResolveSalesHistories::buildSearchCondition( $args );

		return $this->sales_history_service->count( $condition );
	}
}
