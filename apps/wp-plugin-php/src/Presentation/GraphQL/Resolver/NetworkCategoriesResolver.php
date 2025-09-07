<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\UseCase\ResolveNetworkCategories;

class NetworkCategoriesResolver extends ResolverBase {

	private ResolveNetworkCategories $resolve_network_categories;

	public function __construct( ResolveNetworkCategories $resolve_network_categories ) {
		$this->resolve_network_categories = $resolve_network_categories;
	}

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		return $this->resolve_network_categories->handle( $root_value, $args );
	}
}
