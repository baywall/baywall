<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\UseCase\GetChains;
use Baywall\Core\Domain\Entity\Chain;
use Baywall\Core\Domain\ValueObject\ChainId;

class ResolveChains implements Resolver {
	public function __construct(
		private readonly GetChains $get_chains
	) {}

	/** @return mixed[] */
	public function handle( array $root_value, array $args ): array {
		$filter_chain_id = ChainId::fromNullable( $args['filter']['chainId'] ?? null );
		/** @var bool|null */
		$filter_is_connectable = $args['filter']['isConnectable'] ?? null;

		$filtered_chains = $this->get_chains->handle( $filter_chain_id, null, $filter_is_connectable );

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
