<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Interfaces\ValueObject;
use Cornix\Serendipity\Core\Domain\ValueObject\Symbol;

class SymbolPair implements ValueObject {
	private function __construct( Symbol $base_symbol, Symbol $quote_symbol ) {
		$this->base_symbol  = $base_symbol;
		$this->quote_symbol = $quote_symbol;
	}
	public static function from( Symbol $base_symbol, Symbol $quote_symbol ): self {
		return new self( $base_symbol, $quote_symbol );
	}

	private Symbol $base_symbol;
	private Symbol $quote_symbol;

	public function base(): Symbol {
		return $this->base_symbol;
	}

	public function quote(): Symbol {
		return $this->quote_symbol;
	}

	public function equals( self $other ): bool {
		return $this->base_symbol->equals( $other->base_symbol ) && $this->quote_symbol->equals( $other->quote_symbol );
	}

	public function __toString(): string {
		return "{$this->base_symbol->value()}/{$this->quote_symbol->value()}";
	}
}
