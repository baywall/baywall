<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\UseCase\GetIsPluginVersionTooLow;

class ResolveIsPluginVersionTooLow implements Resolver {
	public function __construct( private readonly GetIsPluginVersionTooLow $get_is_plugin_version_too_low ) {}

	public function handle( array $root_value, array $args ): ?bool {
		return $this->get_is_plugin_version_too_low->handle();
	}
}
