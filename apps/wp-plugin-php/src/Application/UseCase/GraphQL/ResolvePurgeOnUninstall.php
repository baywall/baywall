<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase\GraphQL;

use Baywall\Core\Application\Repository\PurgeOnUninstallRepository;
use Baywall\Core\Application\Service\UserAccessChecker;

class ResolvePurgeOnUninstall {


	public function __construct(
		private readonly UserAccessChecker $user_access_checker,
		private readonly PurgeOnUninstallRepository $purge_on_uninstall_repository
	) {}

	public function handle( array $root_value, array $args ): bool {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要
		return $this->purge_on_uninstall_repository->get();
	}
}
