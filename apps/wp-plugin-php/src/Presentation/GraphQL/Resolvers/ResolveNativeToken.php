<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\UseCase\GetNativeToken;
use Baywall\Core\Domain\ValueObject\ChainId;

class ResolveNativeToken implements Resolver {
	public function __construct(
		private readonly GetNativeToken $get_native_token
	) {}

	/** @return array<string, mixed> */
	public function handle( array $root_value, array $args ): array {
		$native_token = $this->get_native_token->handle( ChainId::from( $args['chainId'] ) );

		return array(
			'symbol'   => $native_token->symbol->value(),
			'name'     => $native_token->name,
			'decimals' => $native_token->decimals->value(),
		);
	}
}
