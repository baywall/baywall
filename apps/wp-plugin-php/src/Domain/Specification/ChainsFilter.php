<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Specification;

use Baywall\Core\Domain\Entity\Chain;
use Baywall\Core\Domain\ValueObject\NetworkCategoryId;
use Baywall\Core\Domain\ValueObject\ChainId;

class ChainsFilter {

	private array $filters = array();

	public function byChainId( ChainId $chain_id ): self {
		$this->filters[] = fn ( Chain $chain ) => $chain->id()->equals( $chain_id );
		return $this;
	}

	public function byNetworkCategoryId( NetworkCategoryId $network_category_id ): self {
		$this->filters[] = fn ( Chain $chain ) => $chain->networkCategoryId()->equals( $network_category_id );
		return $this;
	}

	public function byConnectable( ?bool $is_connectable = true ): self {
		$this->filters[] = fn ( Chain $chain ) => $chain->connectable() === $is_connectable;
		return $this;
	}

	/**
	 * フィルタを適用してチェーンの配列を返します。
	 *
	 * @param Chain[] $chains
	 * @return Chain[]
	 */
	public function apply( array $chains ): array {
		foreach ( $this->filters as $filter ) {
			$chains = array_filter( $chains, $filter );
		}
		return array_values( $chains );
	}
}
