<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Domain\Repository\NetworkCategoryRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategory;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategoryId;

class ResolveNetworkCategories {

	private UserAccessChecker $user_access_checker;
	private NetworkCategoryRepository $network_category_repository;

	public function __construct(
		UserAccessChecker $user_access_checker,
		NetworkCategoryRepository $network_category_repository
	) {
		$this->user_access_checker         = $user_access_checker;
		$this->network_category_repository = $network_category_repository;
	}

	public function handle( array $root_value, array $args ): array {
		$this->user_access_checker->checkCanCreatePost();   // 投稿を新規作成できる権限が必要

		// フィルタ対象となるネットワークカテゴリIDを取得
		$filter_network_category_id = NetworkCategoryId::fromNullable( $args['filter']['networkCategoryId'] ?? null );

		// 取得対象となるネットワークカテゴリID一覧
		if ( $filter_network_category_id !== null ) {
			$network_category_ids = array( $filter_network_category_id );
		} else {
			$network_category_ids = array_map(
				fn( NetworkCategory $network_category ) => $network_category->id(),
				$this->network_category_repository->all()
			);
		}

		return array_map(
			fn ( NetworkCategoryId $network_category_id ) => $root_value['networkCategory']( $root_value, array( 'networkCategoryId' => $network_category_id->value() ) ),
			$network_category_ids
		);
	}
}
