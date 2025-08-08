<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Service\TransactionService;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\Confirmations;

/**
 * チェーンのconfirmationsを更新します
 */
class UpdateConfirmations {

	private ChainRepository $chain_repository;
	private TransactionService $transaction_service;

	public function __construct( ChainRepository $chain_repository, TransactionService $transaction_service ) {
		$this->chain_repository    = $chain_repository;
		$this->transaction_service = $transaction_service;
	}


	public function handle( int $chain_id_value, string $confirmations_value ): void {
		$chain_id      = ChainId::from( $chain_id_value );
		$confirmations = Confirmations::from( $confirmations_value );

		try {
			$this->transaction_service->beginTransaction();

			// チェーンのconfirmationsを更新
			$this->updateConfirmations( $chain_id, $confirmations );

			$this->transaction_service->commit();
		} catch ( \Throwable $e ) {
			$this->transaction_service->rollback();
			throw $e;
		}
	}

	private function updateConfirmations( ChainId $chain_id, Confirmations $confirmations ): void {
		$chain = $this->chain_repository->get( $chain_id );
		if ( $chain === null ) {
			throw new \InvalidArgumentException( "[EB90B2E0] Chain with ID {$chain_id} does not exist." );
		}
		$chain->setConfirmations( $confirmations );
		$this->chain_repository->save( $chain );
	}
}
