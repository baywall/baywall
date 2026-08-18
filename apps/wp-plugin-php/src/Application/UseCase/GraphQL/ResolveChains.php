<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase\GraphQL;

use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Domain\Entity\Chain;
use Baywall\Core\Domain\Repository\ChainRepository;
use Baywall\Core\Domain\Specification\ChainsFilter;
use Baywall\Core\Domain\ValueObject\ChainId;

class ResolveChains {

	private UserAccessChecker $user_access_checker;
	private ChainRepository $chain_repository;

	public function __construct(
		UserAccessChecker $user_access_checker,
		ChainRepository $chain_repository
	) {
		$this->user_access_checker = $user_access_checker;
		$this->chain_repository    = $chain_repository;
	}

	public function handle( array $root_value, array $args ): array {
		$this->user_access_checker->checkHasAdminRole();  // 管理者権限が必要

		$filter_chain_id = ChainId::fromNullable( $args['filter']['chainId'] ?? null );
		/** @var bool|null */
		$filter_is_connectable = $args['filter']['isConnectable'] ?? null;

		// 条件に合致するチェーン一覧を取得
		$filter = ( new ChainsFilter() );
		if ( $filter_chain_id !== null ) {
			$filter = $filter->byChainId( $filter_chain_id );
		}
		if ( $filter_is_connectable !== null ) {
			$filter = $filter->byConnectable( $filter_is_connectable );
		}
		$filtered_chains = $filter->apply( $this->chain_repository->all() );

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
