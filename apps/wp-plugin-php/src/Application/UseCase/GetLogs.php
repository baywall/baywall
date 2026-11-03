<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Dto\LogDto;
use Baywall\Core\Application\Port\LogQueryService;
use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Constant\Config;

class GetLogs {
	public function __construct(
		private readonly UserAccessChecker $user_access_checker,
		private readonly LogQueryService $log_query_service
	) {}

	/**
	 * @return LogDto[]
	 */
	public function handle(): array {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		return $this->log_query_service->findRecent( Config::LOGS_MAX_RESULTS );
	}
}
