<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\UseCase\GetChains;
use Baywall\Core\Application\UseCase\GetNetworkCategory;
use Baywall\Core\Application\UseCase\GetSellableSymbols;
use Baywall\Core\Domain\ValueObject\NetworkCategoryId;

class ResolveNetworkCategory implements Resolver {
	public function __construct(
		private readonly GetNetworkCategory $get_network_category,
		private readonly GetChains $get_chains,
		private readonly GetSellableSymbols $get_sellable_symbols
	) {}

	/** @return array<string, mixed> */
	public function handle( array $root_value, array $args ): array {

		$network_category_id = NetworkCategoryId::from( $args['networkCategoryId'] );
		$network_category    = $this->get_network_category->handle( $network_category_id );

		$chains_callback = function () use ( $root_value, $network_category_id ) {
			// ネットワークカテゴリで絞り込んだチェーン一覧を取得
			$chains = $this->get_chains->handle( null, $network_category_id, null );

			return array_map(
				function ( $chain ) use ( $root_value ) {
					return $root_value['chain']( $root_value, array( 'chainId' => $chain->id()->value() ) );
				},
				$chains
			);
		};

		// 対象のネットワークカテゴリで販売可能なシンボル一覧を取得
		$sellable_symbols_callback = function () use ( $network_category_id ) {
			return array_map(
				fn( $symbol ) => $symbol->value(),
				$this->get_sellable_symbols->handle( $network_category_id ),
			);
		};

		return array(
			'id'              => $network_category->id()->value(),
			'name'            => $network_category->name(),
			'chains'          => $chains_callback,
			'sellableSymbols' => $sellable_symbols_callback,
		);
	}
}
