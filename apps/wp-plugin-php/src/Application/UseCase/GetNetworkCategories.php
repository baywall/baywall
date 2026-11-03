<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Application\Repository\NetworkCategoryRepository;
use Baywall\Core\Domain\ValueObject\NetworkCategory;
use Baywall\Core\Domain\ValueObject\NetworkCategoryId;

class GetNetworkCategories {
	public function __construct(
		private readonly UserAccessChecker $user_access_checker,
		private readonly NetworkCategoryRepository $network_category_repository
	) {}

	/**
	 * @return NetworkCategoryId[] 取得対象となるネットワークカテゴリID一覧
	 */
	public function handle( ?NetworkCategoryId $network_category_id ): array {
		$this->user_access_checker->checkCanCreatePost();   // 投稿を新規作成できる権限が必要

		// フィルタ対象となるネットワークカテゴリIDを取得
		if ( $network_category_id !== null ) {
			return array( $network_category_id );
		}

		return array_map(
			fn( NetworkCategory $network_category ) => $network_category->id(),
			$this->network_category_repository->all()
		);
	}
}
