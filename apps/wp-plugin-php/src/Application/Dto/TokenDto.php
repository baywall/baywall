<?php

namespace Cornix\Serendipity\Core\Application\Dto;

use Cornix\Serendipity\Core\Domain\Entity\Token;

class TokenDto {

	public function __construct( int $chain_id, string $address ) {
		$this->chain_id = $chain_id;
		$this->address  = $address;
	}

	public int $chain_id;
	public string $address;
}
