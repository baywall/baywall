<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Domain\Repository\TokenRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;

class ResolveToken {

	private TokenRepository $token_repository;

	public function __construct( TokenRepository $token_repository ) {
		$this->token_repository = $token_repository;
	}

	public function handle( array $root_value, array $args ) {
		$chain_id = ChainId::from( $args['chainId'] );
		$address  = Address::from( $args['address'] );

		$token = $this->token_repository->get( $chain_id, $address );
		if ( $token === null ) {
			throw new \InvalidArgumentException( "[436EAC2D] Token not found for chain ID {$chain_id} and address {$address}" );
		}

		return array(
			'chain'     => fn() => $root_value['chain']( $root_value, array( 'chainId' => $token->chainId()->value() ) ),
			'address'   => $token->address()->value(),
			'symbol'    => $token->symbol()->value(),
			'isPayable' => $token->isPayable(),
		);
	}
}
