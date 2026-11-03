<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Domain\Entity\Chain;
use Baywall\Core\Domain\Repository\ChainRepository;
use Baywall\Core\Domain\ValueObject\ChainId;

class GetChain {
	public function __construct( private readonly ChainRepository $chain_repository ) {}

	public function handle( ChainId $chain_id ): Chain {
		$chain = $this->chain_repository->get( $chain_id );
		if ( null === $chain ) {
			throw new \InvalidArgumentException( "[CA31D9B5] chain data is not found. chain id: {$chain_id}" );
		}
		return $chain;
	}
}
