<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Application\Service\SymbolService;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\Specification\ChainsFilter;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategoryId;
use Cornix\Serendipity\Core\Domain\ValueObject\Symbol;

class NetworkCategoryResolver extends ResolverBase {

	public function __construct(
		ChainRepository $chain_repository,
		UserAccessChecker $user_access_checker,
		SymbolService $symbol_service
	) {
		$this->chain_repository    = $chain_repository;
		$this->user_access_checker = $user_access_checker;
		$this->symbol_service      = $symbol_service;
	}
	private ChainRepository $chain_repository;
	private UserAccessChecker $user_access_checker;
	private SymbolService $symbol_service;

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		$network_category_id = NetworkCategoryId::from( $args['networkCategoryId'] );

		$sellable_symbols_callback = function () {
			$this->user_access_checker->checkCanCreatePost();   // 投稿を新規作成できる権限が必要

			return array_map(
				fn( Symbol $symbol ) => $symbol->value(),
				$this->symbol_service->getSellableSymbols()
			);
		};

		// ネットワークカテゴリで絞り込んだチェーン一覧を取得
		$chains_filter = ( new ChainsFilter() )->byNetworkCategoryId( $network_category_id );
		$chains        = $chains_filter->apply( $this->chain_repository->all() );

		$chains_callback = function () use ( $root_value, $chains ) {
			return array_map(
				function ( $chain ) use ( $root_value ) {
					return $root_value['chain']( $root_value, array( 'chainId' => $chain->id()->value() ) );
				},
				$chains
			);
		};

		return array(
			'id'              => $network_category_id->value(),
			'chains'          => $chains_callback,
			'sellableSymbols' => $sellable_symbols_callback,
		);
	}
}
