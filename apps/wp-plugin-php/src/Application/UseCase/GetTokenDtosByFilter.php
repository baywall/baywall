<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Dto\TokenDto;
use Cornix\Serendipity\Core\Application\Dto\TokenDtoAssembler;
use Cornix\Serendipity\Core\Domain\Entity\Token;
use Cornix\Serendipity\Core\Domain\Repository\TokenRepository;
use Cornix\Serendipity\Core\Domain\Specification\TokensFilter;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;

/** 指定したチェーンIDに存在するトークン一覧を取得します */
class GetTokenDtosByFilter {

	public function __construct( TokenRepository $token_repository ) {
		$this->token_repository = $token_repository;
	}

	private TokenRepository $token_repository;

	/** @return TokenDto[] */
	public function handle( ?int $filter_chain_id_value, ?string $filter_address_value ): array {
		$filter = new TokensFilter();

		if ( $filter_chain_id_value !== null ) {
			$filter = $filter->byChainId( ChainId::from( $filter_chain_id_value ) );
		}
		if ( $filter_address_value !== null ) {
			$filter = $filter->byAddress( Address::from( $filter_address_value ) );
		}

		return array_map(
			fn( Token $token ) => TokenDtoAssembler::fromEntity( $token ),
			$filter->apply( $this->token_repository->all() )
		);
	}
}
