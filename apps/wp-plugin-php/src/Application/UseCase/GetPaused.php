<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Domain\Repository\PausedRepository;

class GetPaused {
	public function __construct(
		private readonly UserAccessChecker $user_access_checker,
		private readonly PausedRepository $paused_repository
	) {}

	public function handle(): bool {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要
		return $this->paused_repository->get();
	}
}
