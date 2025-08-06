<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\Confirmations;

/**
 * チェーンのconfirmationsを更新します
 */
class UpdateConfirmations {
	public function __construct( ChainRepository $chain_repository ) {
		$this->chain_repository = $chain_repository;
	}

	private ChainRepository $chain_repository;

	public function handle( int $chain_id_value, string $confirmations_value ): void {
		$chain_id      = ChainId::from( $chain_id_value );
		$confirmations = Confirmations::from( $confirmations_value );

		$chain = $this->chain_repository->get( $chain_id );
		if ( $chain === null ) {
			throw new \InvalidArgumentException( "[EB90B2E0] Chain with ID {$chain_id} does not exist." );
		}
		$chain->setConfirmations( $confirmations );
		$this->chain_repository->save( $chain );
	}
}
