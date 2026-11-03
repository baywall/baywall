<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\UseCase\GetOracle;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\ChainId;

class ResolveOracle implements Resolver {
	public function __construct(
		private readonly GetOracle $get_oracle
	) {}

	/** @return array<string, mixed> */
	public function handle( array $root_value, array $args ): array {
		$oracle = $this->get_oracle->handle(
			ChainId::from( $args['chainId'] ),
			Address::from( $args['address'] )
		);

		$chain_callback = fn() => $root_value['chain'](
			$root_value,
			array(
				'chainId' => $oracle->chainId()->value(),
			)
		);

		return array(
			'chain'       => $chain_callback,
			'address'     => $oracle->address()->value(),
			'baseSymbol'  => $oracle->symbolPair()->base()->value(),
			'quoteSymbol' => $oracle->symbolPair()->quote()->value(),
		);
	}
}
