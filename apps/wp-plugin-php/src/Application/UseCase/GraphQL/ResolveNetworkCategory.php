<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\Repository\NetworkCategoryRepository;
use Cornix\Serendipity\Core\Domain\Specification\ChainsFilter;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategoryId;

class ResolveNetworkCategory {

	private ChainRepository $chain_repository;
	private NetworkCategoryRepository $network_category_repository;

	public function __construct(
		ChainRepository $chain_repository,
		NetworkCategoryRepository $network_category_repository
	) {
		$this->chain_repository            = $chain_repository;
		$this->network_category_repository = $network_category_repository;
	}

	public function handle( array $root_value, array $args ): array {

		$network_category = $this->network_category_repository->get( NetworkCategoryId::from( $args['networkCategoryId'] ) );

		$chains_callback = function () use ( $root_value, $network_category ) {
			// ネットワークカテゴリで絞り込んだチェーン一覧を取得
			$chains_filter = ( new ChainsFilter() )->byNetworkCategoryId( $network_category->id() );
			$chains        = $chains_filter->apply( $this->chain_repository->all() );

			return array_map(
				function ( $chain ) use ( $root_value ) {
					return $root_value['chain']( $root_value, array( 'chainId' => $chain->id()->value() ) );
				},
				$chains
			);
		};

		return array(
			'id'     => $network_category->id()->value(),
			'name'   => $network_category->name(),
			'chains' => $chains_callback,
		);
	}
}
