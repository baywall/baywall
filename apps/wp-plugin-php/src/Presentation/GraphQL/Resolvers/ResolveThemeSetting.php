<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\UseCase\GetThemeSetting;

class ResolveThemeSetting implements Resolver {
	public function __construct( private readonly GetThemeSetting $get_theme_setting ) {}

	public function handle( array $root_value, array $args ): string {
		return $this->get_theme_setting->handle();
	}
}
