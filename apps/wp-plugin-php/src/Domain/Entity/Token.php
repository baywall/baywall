<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Entity;

use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Infrastructure\Database\ValueObject\TokenTableRecord;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\Decimals;
use Cornix\Serendipity\Core\Domain\ValueObject\Symbol;

class Token {

	public function __construct( ChainId $chain_id, Address $address, Symbol $symbol, Decimals $decimals, bool $is_payable ) {
		$this->chain_id   = $chain_id;
		$this->address    = $address;
		$this->symbol     = $symbol;
		$this->decimals   = $decimals;
		$this->is_payable = $is_payable;
	}

	private ChainId $chain_id;
	private Address $address;
	private Symbol $symbol;
	private Decimals $decimals;
	private bool $is_payable;

	public function chainId(): ChainId {
		return $this->chain_id;
	}

	public function address(): Address {
		return $this->address;
	}

	public function symbol(): Symbol {
		return $this->symbol;
	}

	public function decimals(): Decimals {
		return $this->decimals;
	}

	public function isPayable(): bool {
		return $this->is_payable;
	}
	public function setIsPayable( bool $is_payable ): void {
		$this->is_payable = $is_payable;
	}

	public function __toString() {
		return json_encode(
			array(
				'chain_id'   => $this->chain_id->value(),
				'address'    => (string) $this->address,
				'symbol'     => $this->symbol->value(),
				'decimals'   => $this->decimals,
				'is_payable' => $this->is_payable,
			)
		);
	}

	public static function fromTableRecord( TokenTableRecord $record ): self {
		return new self(
			ChainId::from( $record->chainIdValue() ),
			Address::from( $record->addressValue() ),
			Symbol::from( $record->symbolValue() ),
			Decimals::from( $record->decimalsValue() ),
			$record->isPayableValue()
		);
	}
}
