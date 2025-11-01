<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\Repository\NetworkCategoryRepository;
use Cornix\Serendipity\Core\Domain\Service\SymbolService;
use Cornix\Serendipity\Core\Domain\Specification\ChainsFilter;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategoryId;

class ResolveNetworkCategory {

	private UserAccessChecker $user_access_checker;
	private ChainRepository $chain_repository;
	private NetworkCategoryRepository $network_category_repository;
	private SymbolService $symbol_service;

	public function __construct(
		UserAccessChecker $user_access_checker,
		ChainRepository $chain_repository,
		NetworkCategoryRepository $network_category_repository,
		SymbolService $symbol_service
	) {
		$this->user_access_checker         = $user_access_checker;
		$this->chain_repository            = $chain_repository;
		$this->network_category_repository = $network_category_repository;
		$this->symbol_service              = $symbol_service;
	}

	public function handle( array $root_value, array $args ): array {

		$network_category_id = NetworkCategoryId::from( $args['networkCategoryId'] );

		$chains_callback = function () use ( $root_value, $network_category_id ) {
			// ネットワークカテゴリで絞り込んだチェーン一覧を取得
			$chains_filter = ( new ChainsFilter() )->byNetworkCategoryId( $network_category_id );
			$chains        = $chains_filter->apply( $this->chain_repository->all() );

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

			$all_symbols      = $this->symbol_service->all();
			$sellable_symbols = array_filter(
				$all_symbols,
				fn( $symbol ) => $this->symbol_service->isSellable( $symbol, $network_category_id ),
			);
			return array_map(
				fn( $symbol ) => $symbol->value(),
				$sellable_symbols,
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
