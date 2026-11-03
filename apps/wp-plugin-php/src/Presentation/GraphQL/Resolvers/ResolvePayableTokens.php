<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\UseCase\GetPayableTokens;
use Baywall\Core\Domain\Entity\Token;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\PostId;

class ResolvePayableTokens implements Resolver {
	public function __construct(
		private readonly GetPayableTokens $get_payable_tokens
	) {}

	/** @return mixed[] */
	public function handle( array $root_value, array $args ) {
		$tokens = $this->get_payable_tokens->handle(
			PostId::from( $args['args']['postId'] ),
			Address::fromNullable( $args['args']['buyerAddress'] ?? null )
		);

		return array_map(
			fn( Token $token ) => $root_value['token'](
				$root_value,
				array(
					'chainId' => $token->chainId()->value(),
					'address' => $token->address()->value(),
				)
			),
			$tokens
		);
	}
}
