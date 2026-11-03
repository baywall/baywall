<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\UseCase\GetPurgeOnUninstall;

class ResolvePurgeOnUninstall implements Resolver {
	public function __construct(
		private readonly GetPurgeOnUninstall $get_purge_on_uninstall
	) {}

	public function handle( array $root_value, array $args ): bool {
		return $this->get_purge_on_uninstall->handle();
	}
}
