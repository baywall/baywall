<?php

namespace Cornix\Serendipity\Core\Application\Dto;

class AppContractDto {

	public function __construct( string $address ) {
		$this->address = $address;
	}

	public string $address;
}
