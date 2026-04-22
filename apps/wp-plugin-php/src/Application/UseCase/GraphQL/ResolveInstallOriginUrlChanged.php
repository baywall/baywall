<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Domain\Service\SiteService;

class ResolveInstallOriginUrlChanged {

	private SiteService $site_service;

	public function __construct(
		SiteService $site_service
	) {
		$this->site_service = $site_service;
	}

	public function handle( array $root_value, array $args ): bool {
		// アクセス制御は不要
		return $this->site_service->isInstallOriginUrlChanged();
	}
}
