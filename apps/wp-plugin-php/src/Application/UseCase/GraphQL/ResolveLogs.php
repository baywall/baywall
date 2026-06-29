<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Application\Dto\LogDto;
use Cornix\Serendipity\Core\Application\Service\LogQueryService;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Constant\Config;

class ResolveLogs {

	private UserAccessChecker $user_access_checker;
	private LogQueryService $log_query_service;

	public function __construct(
		UserAccessChecker $user_access_checker,
		LogQueryService $log_query_service
	) {
		$this->user_access_checker = $user_access_checker;
		$this->log_query_service   = $log_query_service;
	}

	public function handle( array $root_value, array $args ) {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		$logs = $this->log_query_service->findRecent( Config::LOGS_MAX_RESULTS );

		return array_map(
			function ( LogDto $log ) {
				return array(
					'id'        => $log->id,
					'createdAt' => $log->createdAt,
					'level'     => $log->level,
					'category'  => $log->category,
					'message'   => $log->message,
				);
			},
			$logs
		);
	}
}
