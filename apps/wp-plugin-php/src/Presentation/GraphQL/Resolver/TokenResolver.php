<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Domain\Repository\TokenRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;

class TokenResolver extends ResolverBase {

	public function __construct( TokenRepository $token_repository ) {
		$this->token_repository = $token_repository;
	}

	private TokenRepository $token_repository;

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		$chain_id = ChainId::from( $args['chainID'] );
		$address  = Address::from( $args['address'] );

		$token = $this->token_repository->get( $chain_id, $address );

		return array(
			'chain'     => fn() => $root_value['chain']( $root_value, array( 'chainID' => $chain_id->value() ) ),
			'address'   => $address->value(),
			'symbol'    => fn() => $token->symbol()->value(),
			'isPayable' => fn() => $token->isPayable(),
		);
	}
}
