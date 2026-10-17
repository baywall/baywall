<?php
declare(strict_types=1);

namespace Baywall\Core\Domain\Entity;

use Baywall\Core\Domain\Entity\Chain;
use Baywall\Core\Domain\ValueObject\Address;

class AppContract {
	protected function __construct(
		private readonly Chain $chain,
		private readonly Address $address
	) {}


	public function chain(): Chain {
		return $this->chain;
	}
	public function address(): Address {
		return $this->address;
	}
}
