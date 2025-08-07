<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Dto\ChainDto;
use Cornix\Serendipity\Core\Application\Dto\ChainDtoAssembler;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;

/** 指定したチェーンIDの情報を取得します */
class GetChainDto {

	public function __construct( ChainRepository $chain_repository ) {
		$this->chain_repository = $chain_repository;
	}

	private ChainRepository $chain_repository;

	public function handle( int $chain_id_value ): ChainDto {
		$chain_id = ChainId::from( $chain_id_value );

		$chain = $this->chain_repository->get( $chain_id );
		assert( null !== $chain, "[A28A6F15] chain data is not found. chain id: {$chain_id_value}" );
		return ChainDtoAssembler::fromEntity( $chain );
	}
}
