<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase\GraphQL;

use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Domain\Entity\Oracle;
use Baywall\Core\Domain\Repository\OracleRepository;

class ResolveOracles {


	public function __construct(
		private readonly UserAccessChecker $user_access_checker,
		private readonly OracleRepository $oracle_repository
	) {}

	public function handle( array $root_value, array $args ): array {
		$this->user_access_checker->checkHasAdminRole();  // 管理者権限が必要

		$oracles = $this->oracle_repository->all();

		return array_map(
			fn( Oracle $oracle ) => $root_value['oracle'](
				$root_value,
				array(
					'chainId' => $oracle->chainId()->value(),
					'address' => $oracle->address()->value(),
				)
			),
			$oracles
		);
	}
}
