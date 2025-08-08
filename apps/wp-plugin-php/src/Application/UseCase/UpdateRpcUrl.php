<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Service\TransactionService;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\RpcUrl;

/**
 * チェーンのrpc_urlを更新します
 */
class UpdateRpcUrl {

	private ChainRepository $chain_repository;
	private TransactionService $transaction_service;

	public function __construct( ChainRepository $chain_repository, TransactionService $transaction_service ) {
		$this->chain_repository    = $chain_repository;
		$this->transaction_service = $transaction_service;
	}


	public function handle( int $chain_id_value, ?string $rpc_url_value ): void {
		$chain_id = ChainId::from( $chain_id_value );
		$rpc_url  = RpcUrl::from( $rpc_url_value );

		try {
			$this->transaction_service->beginTransaction();

			// チェーンのRPC URLを更新
			$this->updateRpcUrl( $chain_id, $rpc_url );

			$this->transaction_service->commit();
		} catch ( \Throwable $e ) {
			$this->transaction_service->rollback();
			throw $e;
		}
	}

	private function updateRpcUrl( ChainId $chain_id, ?RpcUrl $rpc_url ): void {
		$chain = $this->chain_repository->get( $chain_id );
		assert( $chain !== null, "[99179250] Chain with ID {$chain_id} does not exist." );

		$chain->setRpcUrl( $rpc_url );
		$this->chain_repository->save( $chain );
	}
}
