<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\UseCase\GetOracles;
use Baywall\Core\Domain\Entity\Oracle;

class ResolveOracles implements Resolver {
	public function __construct(
		private readonly GetOracles $get_oracles
	) {}

	/** @return mixed[] */
	public function handle( array $root_value, array $args ): array {
		$oracles = $this->get_oracles->handle();

		return array_map(
			fn( Oracle $oracle ) => $root_value['oracle'](
				$root_value,
				array(
					'chainId' => $oracle->chainId()->value(),
					'address' => $oracle->address()->value(),
				)
			),
			$oracles
		);
	}
}
