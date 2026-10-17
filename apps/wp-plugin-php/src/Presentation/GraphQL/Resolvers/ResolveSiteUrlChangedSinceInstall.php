<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Domain\Service\SiteService;

class ResolveSiteUrlChangedSinceInstall {


	public function __construct( private readonly SiteService $site_service ) {}

	public function handle( array $root_value, array $args ): bool {
		// アクセス制御は不要
		return $this->site_service->hasSiteUrlChangedSinceInstall();
	}
}
