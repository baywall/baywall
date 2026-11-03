<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Domain\Entity\Oracle;
use Baywall\Core\Domain\Repository\OracleRepository;

class GetOracles {
	public function __construct(
		private readonly UserAccessChecker $user_access_checker,
		private readonly OracleRepository $oracle_repository
	) {}

	/**
	 * @return Oracle[]
	 */
	public function handle(): array {
		$this->user_access_checker->checkHasAdminRole();  // 管理者権限が必要

		return $this->oracle_repository->all();
	}
}
