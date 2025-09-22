<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Domain\Entity\Oracle;
use Cornix\Serendipity\Core\Domain\Repository\OracleRepository;

class ResolveOracles {

	private UserAccessChecker $user_access_checker;
	private OracleRepository $oracle_repository;

	public function __construct(
		UserAccessChecker $user_access_checker,
		OracleRepository $oracle_repository
	) {
		$this->user_access_checker = $user_access_checker;
		$this->oracle_repository   = $oracle_repository;
	}

	public function handle( array $root_value, array $args ): array {
		$this->user_access_checker->checkHasAdminRole();  // 管理者権限が必要

		$oracles = $this->oracle_repository->all();

		return array_map(
			fn( Oracle $oracle ) => $root_value['oracle'](
				$root_value,
				array(
					'chainId' => $oracle->chain()->id()->value(),
					'address' => $oracle->address()->value(),
				)
			),
			$oracles
		);
	}
}
