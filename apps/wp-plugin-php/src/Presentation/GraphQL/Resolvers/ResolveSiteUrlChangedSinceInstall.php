<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\UseCase\GetSiteUrlChangedSinceInstall;

class ResolveSiteUrlChangedSinceInstall implements Resolver {
	public function __construct( private readonly GetSiteUrlChangedSinceInstall $get_site_url_changed_since_install ) {}

	public function handle( array $root_value, array $args ): bool {
		return $this->get_site_url_changed_since_install->handle();
	}
}
