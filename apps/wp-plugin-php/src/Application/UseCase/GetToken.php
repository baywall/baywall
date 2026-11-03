<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Domain\Entity\Token;
use Baywall\Core\Domain\Repository\TokenRepository;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\ChainId;

class GetToken {
	public function __construct( private readonly TokenRepository $token_repository ) {}

	public function handle( ChainId $chain_id, Address $address ): Token {
		$token = $this->token_repository->get( $chain_id, $address );
		if ( $token === null ) {
			throw new \InvalidArgumentException( "[436EAC2D] Token not found for chain ID {$chain_id} and address {$address}" );
		}
		return $token;
	}
}
