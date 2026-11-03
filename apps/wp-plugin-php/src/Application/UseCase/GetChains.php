<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Domain\Entity\Chain;
use Baywall\Core\Domain\Repository\ChainRepository;
use Baywall\Core\Domain\Specification\ChainsSpecification;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\NetworkCategoryId;

class GetChains {
	public function __construct(
		private readonly UserAccessChecker $user_access_checker,
		private readonly ChainRepository $chain_repository
	) {}

	/**
	 * @return Chain[]
	 */
	public function handle( ?ChainId $chain_id, ?NetworkCategoryId $network_category_id, ?bool $is_connectable ): array {
		$this->user_access_checker->checkHasAdminRole();  // 管理者権限が必要

		// 条件に合致するチェーン一覧を取得（条件が null の場合は判定しない）
		return array_filter(
			$this->chain_repository->all(),
			( new ChainsSpecification( chain_id: $chain_id, network_category_id: $network_category_id, is_connectable: $is_connectable ) )->isSatisfiedBy( ... )
		);
	}
}
