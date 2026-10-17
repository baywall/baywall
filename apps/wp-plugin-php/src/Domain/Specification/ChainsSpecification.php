<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Specification;

use Baywall\Core\Domain\Entity\Chain;
use Baywall\Core\Domain\ValueObject\NetworkCategoryId;
use Baywall\Core\Domain\ValueObject\ChainId;

class ChainsSpecification {

	/**
	 * @param bool|null $is_connectable 接続可能かどうか。null の場合は接続可能性を判定しない。
	 */
	public function __construct(
		private readonly ?ChainId $chain_id = null,
		private readonly ?NetworkCategoryId $network_category_id = null,
		private readonly ?bool $is_connectable = null,
	) {}

	public function isSatisfiedBy( Chain $chain ): bool {
		if ( null !== $this->chain_id && ! $chain->id()->equals( $this->chain_id ) ) {
			return false;
		}
		if ( null !== $this->network_category_id && ! $chain->networkCategoryId()->equals( $this->network_category_id ) ) {
			return false;
		}
		return null === $this->is_connectable || $chain->connectable() === $this->is_connectable;
	}
}
