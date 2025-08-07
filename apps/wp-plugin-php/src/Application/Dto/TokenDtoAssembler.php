<?php

namespace Cornix\Serendipity\Core\Application\Dto;

use Cornix\Serendipity\Core\Domain\Entity\Token;

class TokenDtoAssembler {

	public static function fromEntity( Token $token ): TokenDto {
		return new TokenDto(
			$token->chainId()->value(),
			$token->address()->value(),
			$token->symbol()->value(),
			$token->isPayable()
		);
	}
}
