<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\UseCase\GetTokenDtosByFilter;

class ResolveToken {

	private GetTokenDtosByFilter $get_token_dtos_by_chain_id_value;

	public function __construct( GetTokenDtosByFilter $get_token_dtos_by_chain_id_value ) {
		$this->get_token_dtos_by_chain_id_value = $get_token_dtos_by_chain_id_value;
	}

	public function handle( array $root_value, array $args ) {
		/** @var int */
		$chain_id_value = $args['chainId'];
		/** @var string */
		$address_value = $args['address'];

		$token_dtos = $this->get_token_dtos_by_chain_id_value->handle( $chain_id_value, $address_value );
		if ( count( $token_dtos ) !== 1 ) {
			throw new \InvalidArgumentException( "[3716EBA8] Expected exactly one token for chain ID {$chain_id_value} and address {$address_value}, found " . count( $token_dtos ) );
		}
		$token_dto = array_values( $token_dtos )[0];

		return array(
			'chain'     => fn() => $root_value['chain']( $root_value, array( 'chainId' => $token_dto->chain_id ) ),
			'address'   => $token_dto->address,
			'symbol'    => $token_dto->symbol,
			'isPayable' => $token_dto->is_payable,
		);
	}
}
