<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Domain\Repository\ChainRepository;
use Baywall\Core\Domain\Repository\NetworkCategoryRepository;
use Baywall\Core\Domain\Service\SymbolService;
use Baywall\Core\Domain\Specification\ChainsSpecification;
use Baywall\Core\Domain\ValueObject\NetworkCategoryId;

class ResolveNetworkCategory {


	public function __construct(
		private readonly UserAccessChecker $user_access_checker,
		private readonly ChainRepository $chain_repository,
		private readonly NetworkCategoryRepository $network_category_repository,
		private readonly SymbolService $symbol_service
	) {}

	public function handle( array $root_value, array $args ): array {

		$network_category_id = NetworkCategoryId::from( $args['networkCategoryId'] );

		$chains_callback = function () use ( $root_value, $network_category_id ) {
			// ネットワークカテゴリで絞り込んだチェーン一覧を取得
			$chains = array_filter(
				$this->chain_repository->all(),
				( new ChainsSpecification( network_category_id: $network_category_id ) )->isSatisfiedBy( ... )
			);

			return array_map(
				function ( $chain ) use ( $root_value ) {
					return $root_value['chain']( $root_value, array( 'chainId' => $chain->id()->value() ) );
				},
				$chains
			);
		};

		// 対象のネットワークカテゴリで販売可能なシンボル一覧を取得
		$sellable_symbols_callback = function () use ( $network_category_id ) {
			$this->user_access_checker->checkCanCreatePost();   // 投稿を新規作成できる権限が必要

			return array_map(
				fn( $symbol ) => $symbol->value(),
				$this->symbol_service->sellableSymbols( $network_category_id ),
			);
		};

		return array(
			'id'              => $network_category_id->value(),
			'name'            => $this->network_category_repository->get( $network_category_id )->name(),
			'chains'          => $chains_callback,
			'sellableSymbols' => $sellable_symbols_callback,
		);
	}
}
