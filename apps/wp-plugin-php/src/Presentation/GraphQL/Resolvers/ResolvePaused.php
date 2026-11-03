<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\UseCase\GetPaused;

class ResolvePaused implements Resolver {
	public function __construct( private readonly GetPaused $get_paused ) {}

	public function handle( array $root_value, array $args ): bool {
		return $this->get_paused->handle();
	}
}
