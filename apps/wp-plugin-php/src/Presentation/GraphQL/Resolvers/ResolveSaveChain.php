<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\Exception\ChainIdMismatchException;
use Baywall\Core\Application\Service\TransactionService;
use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Domain\Repository\ChainRepository;
use Baywall\Core\Domain\Service\BlockchainService;
use Baywall\Core\Domain\ValueObject\ChainId;
use Baywall\Core\Domain\ValueObject\Confirmations;
use Baywall\Core\Domain\ValueObject\MaxLogsRange;
use Baywall\Core\Domain\ValueObject\RpcUrl;

class ResolveSaveChain {


	public function __construct(
		private readonly UserAccessChecker $user_access_checker,
		private readonly ChainRepository $chain_repository,
		private readonly TransactionService $transaction_service,
		private readonly BlockchainService $blockchain_service
	) {}

	public function handle( array $root_value, array $args ): bool {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		$chain_id = ChainId::from( $args['chainId'] );
		$rpc_url  = RpcUrl::fromNullable( $args['rpcUrl'] );
		/** @var int */
		$confirmations_value = $args['confirmations'];
		$confirmations       = Confirmations::from( $confirmations_value );
		$max_logs_range      = MaxLogsRange::from( $args['maxLogsRange'] );

		return $this->transaction_service->transactional(
			function () use ( $chain_id, $rpc_url, $confirmations, $max_logs_range ) {

				// 更新前のチェーン情報を取得
				$chain = $this->chain_repository->get( $chain_id );
				if ( null === $chain ) {
					throw new \InvalidArgumentException( "[7D681C9D] Chain with ID {$chain_id} does not exist." );
				}

				// RPC URLが別の値になった場合はそのURLが登録しようとしているチェーンIDと一致するかどうかを確認
				if ( $rpc_url !== null && ( $chain->rpcUrl() === null || ! $rpc_url->equals( $chain->rpcUrl() ) ) ) {
					$this->checkChainId( $rpc_url, $chain_id );
				}

				// チェーン情報を更新
				$chain->setRpcUrl( $rpc_url );
				$chain->setConfirmations( $confirmations );
				$chain->setMaxLogsRange( $max_logs_range );

				// 値を更新したチェーン情報を保存
				$this->chain_repository->save( $chain );

				return true;
			}
		);
	}

	/**
	 * RPC URLに接続してチェーンIDを取得し、期待されるチェーンIDと一致するか確認します。
	 * チェーンIDが一致しない場合は例外をスローします。
	 *
	 * @param RpcUrl  $rpc_url
	 * @param ChainId $expected_chain_id
	 * @throws ChainIdMismatchException
	 */
	private function checkChainId( RpcUrl $rpc_url, ChainId $expected_chain_id ): void {
		// RPC URLを使ってブロックチェーンに接続し、チェーンIDを取得
		$actual_chain_id = $this->blockchain_service->getChainId( $rpc_url );

		if ( ! $expected_chain_id->equals( $actual_chain_id ) ) {
			throw new ChainIdMismatchException( "[45BC2FC0] Expected: {$expected_chain_id}, Actual: {$actual_chain_id}" );
		}
	}
}
