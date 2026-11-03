<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\UseCase\GetTokens;
use Baywall\Core\Domain\Entity\Token;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\ChainId;

class ResolveTokens implements Resolver {
	public function __construct(
		private readonly GetTokens $get_tokens
	) {}

	/** @return mixed[] */
	public function handle( array $root_value, array $args ) {
		$filtered_tokens = $this->get_tokens->handle(
			ChainId::fromNullable( $args['filter']['chainId'] ?? null ),
			Address::fromNullable( $args['filter']['address'] ?? null )
		);

		return array_map(
			fn( Token $token ) => $root_value['token'](
				$root_value,
				array(
					'chainId' => $token->chainId()->value(),
					'address' => $token->address()->value(),
				)
			),
			$filtered_tokens
		);
	}
}
