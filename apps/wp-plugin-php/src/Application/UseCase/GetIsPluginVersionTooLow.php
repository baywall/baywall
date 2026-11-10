<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Domain\Service\MinimumSupportedVersionChecker;

class GetIsPluginVersionTooLow {
	public function __construct(
		private readonly UserAccessChecker $user_access_checker,
		private readonly MinimumSupportedVersionChecker $minimum_supported_version_checker
	) {}

	public function handle(): ?bool {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要
		return $this->minimum_supported_version_checker->isBelowMinimumSupportedVersion();
	}
}
