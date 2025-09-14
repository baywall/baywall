<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Application\Service\ChainIdChecker;
use Cornix\Serendipity\Core\Application\Service\TransactionService;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\Confirmations;
use Cornix\Serendipity\Core\Domain\ValueObject\RpcUrl;

class ResolveSaveChain {

	private UserAccessChecker $user_access_checker;
	private ChainRepository $chain_repository;
	private TransactionService $transaction_service;
	private ChainIdChecker $chain_id_checker;

	public function __construct(
		UserAccessChecker $user_access_checker,
		ChainRepository $chain_repository,
		TransactionService $transaction_service,
		ChainIdChecker $chain_id_checker
	) {
		$this->user_access_checker = $user_access_checker;
		$this->chain_repository    = $chain_repository;
		$this->transaction_service = $transaction_service;
		$this->chain_id_checker    = $chain_id_checker;
	}

	public function handle( array $root_value, array $args ): bool {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		$chain_id = ChainId::from( $args['chainId'] );
		$rpc_url  = RpcUrl::fromNullable( $args['rpcUrl'] );
		/** @var string */
		$confirmations_value = $args['confirmations'];
		$confirmations       = Confirmations::from( $confirmations_value );

		try {
			$this->transaction_service->beginTransaction();

			// 更新前のチェーン情報を取得
			$chain = $this->chain_repository->get( $chain_id );

			// RPC URLが別の値になった場合はそのURLが登録しようとしているチェーンIDと一致するかどうかを確認
			if ( $rpc_url !== null && ( $chain->rpcUrl() === null || ! $rpc_url->equals( $chain->rpcUrl() ) ) ) {
				$this->chain_id_checker->checkChainId( $rpc_url, $chain_id );
			}

			// チェーン情報を更新
			$chain->setRpcUrl( $rpc_url );
			$chain->setConfirmations( $confirmations );

			// 値を更新したチェーン情報を保存
			$this->chain_repository->save( $chain );

			$this->transaction_service->commit();
			return true;
		} catch ( \Throwable $e ) {
			$this->transaction_service->rollback();
			throw $e;
		}
	}
}
