<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\UseCase\GetSctaUrl;

class ResolveSctaUrl implements Resolver {
	public function __construct( private readonly GetSctaUrl $get_scta_url ) {}

	public function handle( array $root_value, array $args ): ?string {
		return $this->get_scta_url->handle();
	}
}
