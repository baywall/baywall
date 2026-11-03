<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\UseCase\SaveChain;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\Confirmations;
use Baywall\Core\Domain\ValueObject\MaxLogsRange;
use Baywall\Core\Domain\ValueObject\RpcUrl;

class ResolveSaveChain implements Resolver {
	public function __construct(
		private readonly SaveChain $save_chain
	) {}

	public function handle( array $root_value, array $args ): bool {
		return $this->save_chain->handle(
			ChainId::from( $args['chainId'] ),
			RpcUrl::fromNullable( $args['rpcUrl'] ),
			Confirmations::from( $args['confirmations'] ),
			MaxLogsRange::from( $args['maxLogsRange'] )
		);
	}
}
