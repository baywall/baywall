<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Service\TransactionService;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Infrastructure\Web3\BlockchainClient;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\RpcUrl;

class SetRpcUrlResolver extends ResolverBase {

	public function __construct(
		ChainRepository $chain_repository,
		UserAccessChecker $user_access_checker,
		TransactionService $transaction_service
	) {
		$this->chain_repository    = $chain_repository;
		$this->user_access_checker = $user_access_checker;
		$this->transaction_service = $transaction_service;
	}

	private ChainRepository $chain_repository;
	private UserAccessChecker $user_access_checker;
	private TransactionService $transaction_service;

	/**
	 * #[\Override]
	 *
	 * @return bool
	 */
	public function resolve( array $root_value, array $args ) {
		$chain_id = ChainId::from( $args['chainID'] );
		$rpc_url  = RpcUrl::fromNullable( $args['rpcURL'] ?? null );

		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		// RPC URLを登録する場合は実際にアクセスしてチェーンIDを取得し、
		// 引数のチェーンIDと一致していることを確認する
		if ( ! is_null( $rpc_url ) ) {
			$actual_chain_id = ( new BlockchainClient( $rpc_url ) )->ethChainId();
			if ( ! $chain_id->equals( $actual_chain_id ) ) {
				throw new \InvalidArgumentException( "[0AD91082] Invalid chain ID. expected: {$chain_id}, actual: {$actual_chain_id}" );
			}
		}

		// RPC URLを保存
		try {
			$this->transaction_service->beginTransaction();

			// リポジトリからチェーン情報を取得、RPC URLを設定して保存
			$chain = $this->chain_repository->get( $chain_id );
			$chain->setRpcUrl( $rpc_url );
			$this->chain_repository->save( $chain );

			$this->transaction_service->commit();
		} catch ( \Throwable $e ) {
			$this->transaction_service->rollback();
			throw $e;
		}

		return true;
	}
}
