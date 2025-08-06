<?php

namespace Cornix\Serendipity\Core\Application\Dto;

class ChainDto {

	public function __construct( int $id, ?string $rpc_url, string $confirmations, int $network_category_id ) {
		$this->id                  = $id;
		$this->rpc_url             = $rpc_url;
		$this->confirmations       = $confirmations;
		$this->network_category_id = $network_category_id;
	}

	public int $id;
	public ?string $rpc_url;
	public string $confirmations;
	public int $network_category_id;
}
