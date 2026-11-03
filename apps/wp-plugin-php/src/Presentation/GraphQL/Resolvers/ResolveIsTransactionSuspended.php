<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\UseCase\GetIsTransactionSuspended;

class ResolveIsTransactionSuspended implements Resolver {
	public function __construct( private readonly GetIsTransactionSuspended $get_is_transaction_suspended ) {}

	public function handle( array $root_value, array $args ): bool {
		return $this->get_is_transaction_suspended->handle();
	}
}
