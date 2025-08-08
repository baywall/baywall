<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Application\UseCase\UpdateRpcUrl;
use Cornix\Serendipity\Core\Infrastructure\Web3\BlockchainClient;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Domain\ValueObject\RpcUrl;

class SetRpcUrlResolver extends ResolverBase {

	private UserAccessChecker $user_access_checker;
	private UpdateRpcUrl $update_rpc_url;

	public function __construct(
		UserAccessChecker $user_access_checker,
		UpdateRpcUrl $update_rpc_url
	) {
		$this->user_access_checker = $user_access_checker;
		$this->update_rpc_url      = $update_rpc_url;
	}

	/**
	 * #[\Override]
	 *
	 * @return bool
	 */
	public function resolve( array $root_value, array $args ) {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		/** @var int */
		$chain_id_value = $args['chainID'];
		/** @var string|null */
		$rpc_url_value = $args['rpcURL'];

		$chain_id = ChainId::from( $args['chainID'] );
		$rpc_url  = RpcUrl::fromNullable( $args['rpcURL'] ?? null );

		// RPC URLを登録する場合は実際にアクセスしてチェーンIDを取得し、
		// 引数のチェーンIDと一致していることを確認する
		if ( ! is_null( $rpc_url ) ) {
			$actual_chain_id = ( new BlockchainClient( $rpc_url ) )->ethChainId();
			if ( ! $chain_id->equals( $actual_chain_id ) ) {
				throw new \InvalidArgumentException( "[0AD91082] Invalid chain ID. expected: {$chain_id}, actual: {$actual_chain_id}" );
			}
		}

		// RPC URLを保存
		$this->update_rpc_url->handle( $chain_id_value, $rpc_url_value );

		return true;
	}
}
