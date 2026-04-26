<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Application\Repository\PurgeOnUninstallRepository;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;

class ResolvePurgeOnUninstall {

	private UserAccessChecker $user_access_checker;
	private PurgeOnUninstallRepository $purge_on_uninstall_repository;

	public function __construct( UserAccessChecker $user_access_checker, PurgeOnUninstallRepository $purge_on_uninstall_repository ) {
		$this->user_access_checker           = $user_access_checker;
		$this->purge_on_uninstall_repository = $purge_on_uninstall_repository;
	}

	public function handle( array $root_value, array $args ): bool {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要
		return $this->purge_on_uninstall_repository->get();
	}
}
