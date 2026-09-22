<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Entity;

use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\SymbolPair;

class Oracle {

	public function __construct(
		private readonly ChainId $chain_id,
		private readonly Address $address,
		private readonly SymbolPair $symbol_pair
	) {}


	public function chainId(): ChainId {
		return $this->chain_id;
	}

	public function address(): Address {
		return $this->address;
	}

	public function symbolPair(): SymbolPair {
		return $this->symbol_pair;
	}

	public function __toString() {
		return json_encode(
			array(
				'chain_id'     => $this->chain_id,
				'address'      => $this->address,
				'base_symbol'  => $this->symbol_pair->base()->value(),
				'quote_symbol' => $this->symbol_pair->quote()->value(),
			)
		);
	}
}
