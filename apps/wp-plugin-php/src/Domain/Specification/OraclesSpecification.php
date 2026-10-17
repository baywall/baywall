<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Specification;

use Baywall\Core\Domain\Entity\Oracle;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\SymbolPair;
use Baywall\Core\Domain\ValueObject\ChainId;

class OraclesSpecification {

	/**
	 * @param ChainId[]|null $connectable_chain_ids 接続可能チェーンIDの一覧。null の場合は接続可能性を判定しない。
	 */
	public function __construct(
		private readonly ?ChainId $chain_id = null,
		private readonly ?Address $address = null,
		private readonly ?SymbolPair $symbol_pair = null,
		private readonly ?array $connectable_chain_ids = null,
	) {}

	public function isSatisfiedBy( Oracle $oracle ): bool {
		if ( null !== $this->chain_id && ! $oracle->chainId()->equals( $this->chain_id ) ) {
			return false;
		}
		if ( null !== $this->address && ! $oracle->address()->equals( $this->address ) ) {
			return false;
		}
		if ( null !== $this->symbol_pair && ! $oracle->symbolPair()->equals( $this->symbol_pair ) ) {
			return false;
		}
		return null === $this->connectable_chain_ids || $this->isConnectable( $oracle->chainId() );
	}

	/** ChainId::equals() で判定する（厳密比較では等価だが別インスタンスの ValueObject が弾かれるため）。 */
	private function isConnectable( ChainId $chain_id ): bool {
		foreach ( $this->connectable_chain_ids as $connectable_chain_id ) {
			if ( $chain_id->equals( $connectable_chain_id ) ) {
				return true;
			}
		}
		return false;
	}
}
