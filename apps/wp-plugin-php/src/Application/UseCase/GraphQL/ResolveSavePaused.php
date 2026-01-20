<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Application\Repository\PausedRepository;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;

class ResolveSavePaused {

	private UserAccessChecker $user_access_checker;
	private PausedRepository $paused_repository;

	public function __construct(
		UserAccessChecker $user_access_checker,
		PausedRepository $paused_repository
	) {
		$this->user_access_checker = $user_access_checker;
		$this->paused_repository   = $paused_repository;
	}

	public function handle( array $root_value, array $args ): bool {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		/** @var bool */
		$paused = $args['paused'];
		$this->paused_repository->save( $paused );

		return true;
	}
}
