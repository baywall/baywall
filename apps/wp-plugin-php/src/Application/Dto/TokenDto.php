<?php

namespace Cornix\Serendipity\Core\Application\Dto;

class TokenDto {

	public function __construct( int $chain_id, string $address, string $symbol, bool $is_payable ) {
		$this->chain_id   = $chain_id;
		$this->address    = $address;
		$this->symbol     = $symbol;
		$this->is_payable = $is_payable;
	}

	public int $chain_id;
	public string $address;
	public string $symbol;
	public bool $is_payable;
}
