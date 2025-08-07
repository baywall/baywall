<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Application\UseCase\GetNetworkCategoryIdValuesByFilter;

class NetworkCategoriesResolver extends ResolverBase {

	public function __construct(
		UserAccessChecker $user_access_checker,
		GetNetworkCategoryIdValuesByFilter $get_network_category_id_values_by_filter
	) {
		$this->user_access_checker                      = $user_access_checker;
		$this->get_network_category_id_values_by_filter = $get_network_category_id_values_by_filter;
	}

	private UserAccessChecker $user_access_checker;
	private GetNetworkCategoryIdValuesByFilter $get_network_category_id_values_by_filter;

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		$this->user_access_checker->checkCanCreatePost();   // 投稿を新規作成できる権限が必要

		/** @var array */
		$filter = $args['filter'] ?? null;
		/** @var int|null */
		$filter_network_category = $filter['networkCategoryID'] ?? null;

		return array_map(
			fn ( int $network_category_id_value ) => $root_value['networkCategory']( $root_value, array( 'networkCategoryID' => $network_category_id_value ) ),
			$this->get_network_category_id_values_by_filter->handle( $filter_network_category )
		);
	}
}
