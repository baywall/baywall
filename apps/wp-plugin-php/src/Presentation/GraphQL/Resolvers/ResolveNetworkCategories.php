<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\UseCase\GetNetworkCategories;
use Baywall\Core\Domain\ValueObject\NetworkCategoryId;

class ResolveNetworkCategories implements Resolver {
	public function __construct(
		private readonly GetNetworkCategories $get_network_categories
	) {}

	/** @return mixed[] */
	public function handle( array $root_value, array $args ): array {
		$network_category_ids = $this->get_network_categories->handle(
			NetworkCategoryId::fromNullable( $args['filter']['networkCategoryId'] ?? null )
		);

		return array_map(
			fn ( NetworkCategoryId $network_category_id ) => $root_value['networkCategory']( $root_value, array( 'networkCategoryId' => $network_category_id->value() ) ),
			$network_category_ids
		);
	}
}
