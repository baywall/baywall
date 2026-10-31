<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Domain\Service\SiteUrlChecker;

class ResolveSiteUrlChangedSinceInstall implements Resolver {


	public function __construct( private readonly SiteUrlChecker $site_url_checker ) {}

	public function handle( array $root_value, array $args ): bool {
		// アクセス制御は不要
		return $this->site_url_checker->hasSiteUrlChangedSinceInstall();
	}
}
