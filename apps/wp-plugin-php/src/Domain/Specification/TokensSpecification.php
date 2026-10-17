<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Specification;

use Baywall\Core\Domain\Entity\Token;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\Symbol;

class TokensSpecification {

	/**
	 * @param bool|null $is_payable 支払い可能かどうか。null の場合は支払い可能性を判定しない。
	 */
	public function __construct(
		private readonly ?ChainId $chain_id = null,
		private readonly ?Address $address = null,
		private readonly ?Symbol $symbol = null,
		private readonly ?bool $is_payable = null,
	) {}

	public function isSatisfiedBy( Token $token ): bool {
		if ( null !== $this->chain_id && ! $token->chainId()->equals( $this->chain_id ) ) {
			return false;
		}
		if ( null !== $this->address && ! $token->address()->equals( $this->address ) ) {
			return false;
		}
		if ( null !== $this->symbol && ! $token->symbol()->equals( $this->symbol ) ) {
			return false;
		}
		return null === $this->is_payable || $token->isPayable() === $this->is_payable;
	}
}
