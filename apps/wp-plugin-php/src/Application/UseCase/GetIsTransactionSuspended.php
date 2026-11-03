<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Domain\Service\SiteStatusService;

class GetIsTransactionSuspended {
	public function __construct( private readonly SiteStatusService $site_status_service ) {}

	public function handle(): bool {
		// アクセス制御は不要
		return $this->site_status_service->isTransactionSuspended();
	}
}
