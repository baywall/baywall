<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Specification;

use Baywall\Core\Domain\Entity\Oracle;
use Baywall\Core\Domain\Repository\ChainRepository;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\SymbolPair;
use Baywall\Core\Domain\ValueObject\ChainId;

class OraclesFilter {

	private ChainRepository $chain_repository;
	private array $filters = array();

	public function __construct( ChainRepository $chain_repository ) {
		$this->chain_repository = $chain_repository;
	}

	public function byChainId( ChainId $chain_id ): self {
		$this->filters[] = fn ( Oracle $oracle ) => $oracle->chainId()->equals( $chain_id );
		return $this;
	}
	public function byAddress( Address $address ): self {
		$this->filters[] = fn ( Oracle $oracle ) => $oracle->address()->equals( $address );
		return $this;
	}
	public function bySymbolPair( SymbolPair $symbol_pair ): self {
		$this->filters[] = fn ( Oracle $oracle ) => $oracle->symbolPair()->equals( $symbol_pair );
		return $this;
	}
	public function byConnectable(): self {
		$this->filters[] = fn ( Oracle $oracle ) => $this->chain_repository->get( $oracle->chainId() )->connectable();
		return $this;
	}

	/**
	 * フィルタを適用した結果を返します。
	 *
	 * @param Oracle[] $oracles
	 * @return Oracle[]
	 */
	public function apply( array $oracles ): array {
		foreach ( $this->filters as $filter ) {
			$oracles = array_filter( $oracles, $filter );
		}
		return $oracles;
	}
}
