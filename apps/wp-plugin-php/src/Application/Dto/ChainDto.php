<?php

namespace Cornix\Serendipity\Core\Application\Dto;

class ChainDto {

	public function __construct( int $id, string $name, ?string $rpc_url, string $confirmations, int $network_category_id, ?string $block_explorer_url ) {
		$this->id                  = $id;
		$this->name                = $name;
		$this->rpc_url             = $rpc_url;
		$this->confirmations       = $confirmations;
		$this->network_category_id = $network_category_id;
		$this->block_explorer_url  = $block_explorer_url;
	}

	public int $id;
	public string $name;
	public ?string $rpc_url;
	public string $confirmations;
	public int $network_category_id;
	public ?string $block_explorer_url;
}
