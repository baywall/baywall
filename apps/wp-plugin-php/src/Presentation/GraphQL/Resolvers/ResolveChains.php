<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Domain\Entity\Chain;
use Baywall\Core\Domain\Repository\ChainRepository;
use Baywall\Core\Domain\Specification\ChainsSpecification;
use Baywall\Core\Domain\ValueObject\ChainId;

class ResolveChains {


	public function __construct(
		private readonly UserAccessChecker $user_access_checker,
		private readonly ChainRepository $chain_repository
	) {}

	public function handle( array $root_value, array $args ): array {
		$this->user_access_checker->checkHasAdminRole();  // 管理者権限が必要

		$filter_chain_id = ChainId::fromNullable( $args['filter']['chainId'] ?? null );
		/** @var bool|null */
		$filter_is_connectable = $args['filter']['isConnectable'] ?? null;

		// 条件に合致するチェーン一覧を取得（条件が null の場合は判定しない）
		$filtered_chains = array_filter(
			$this->chain_repository->all(),
			( new ChainsSpecification( chain_id: $filter_chain_id, is_connectable: $filter_is_connectable ) )->isSatisfiedBy( ... )
		);

		return array_map(
			fn( Chain $chain ) => $root_value['chain'](
				$root_value,
				array(
					'chainId' => $chain->id()->value(),
				)
			),
			$filtered_chains
		);
	}
}
