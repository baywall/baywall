<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\UseCase\SaveOracle;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\ChainId;

class ResolveSaveOracle implements Resolver {
	public function __construct(
		private readonly SaveOracle $save_oracle
	) {}

	/** @return bool */
	public function handle( array $root_value, array $args ) {
		return $this->save_oracle->handle(
			ChainId::from( $args['chainId'] ),
			Address::from( $args['address'] )
		);
	}
}
