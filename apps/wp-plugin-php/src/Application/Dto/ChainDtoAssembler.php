<?php

namespace Cornix\Serendipity\Core\Application\Dto;

use Cornix\Serendipity\Core\Domain\Entity\Chain;

class ChainDtoAssembler {

	public static function fromEntity( Chain $chain ): ChainDto {
		return new ChainDto(
			$chain->id()->value(),
			$chain->name(),
			$chain->rpcUrl() ? $chain->rpcUrl()->value() : null,
			(string) $chain->confirmations()->value(),
			$chain->networkCategoryId()->value(),
			$chain->blockExplorerUrl()
		);
	}
}
