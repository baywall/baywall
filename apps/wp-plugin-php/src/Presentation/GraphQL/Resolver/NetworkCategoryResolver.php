<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\UseCase\ResolveNetworkCategory;

class NetworkCategoryResolver extends ResolverBase {

	private ResolveNetworkCategory $resolve_network_category;
	public function __construct( ResolveNetworkCategory $resolve_network_category ) {
		$this->resolve_network_category = $resolve_network_category;
	}

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		return $this->resolve_network_category->handle( $root_value, $args );
	}
}
