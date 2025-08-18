<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Service\ChainIdChecker;
use Cornix\Serendipity\Core\Application\Service\TransactionService;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\Confirmations;
use Cornix\Serendipity\Core\Domain\ValueObject\RpcUrl;

/**
 * チェーン情報を保存します
 */
class SaveChain {

	private ChainRepository $chain_repository;
	private TransactionService $transaction_service;
	private ChainIdChecker $chain_id_checker;

	public function __construct(
		ChainRepository $chain_repository,
		TransactionService $transaction_service,
		ChainIdChecker $chain_id_checker
	) {
		$this->chain_repository    = $chain_repository;
		$this->transaction_service = $transaction_service;
		$this->chain_id_checker    = $chain_id_checker;
	}


	public function handle( int $chain_id_value, ?string $rpc_url_value, string $confirmations_value ): void {
		$chain_id      = ChainId::from( $chain_id_value );
		$rpc_url       = RpcUrl::fromNullable( $rpc_url_value );
		$confirmations = Confirmations::from( $confirmations_value );

		try {
			$this->transaction_service->beginTransaction();

			// 更新前のチェーン情報を取得
			$chain = $this->chain_repository->get( $chain_id );

			// RPC URLが別の値になった場合はそのURLが登録しようとしているチェーンIDと一致するかどうかを確認
			if ( $rpc_url !== null && ( $chain->rpcUrl() === null || ! $rpc_url->equals( $chain->rpcUrl() ) ) ) {
				$this->chain_id_checker->checkChainId( $rpc_url->value(), $chain_id_value );
			}

			// チェーン情報を更新
			$chain->setRpcUrl( $rpc_url );
			$chain->setConfirmations( $confirmations );

			// 値を更新したチェーン情報を保存
			$this->chain_repository->save( $chain );

			$this->transaction_service->commit();
		} catch ( \Throwable $e ) {
			$this->transaction_service->rollback();
			throw $e;
		}
	}
}
