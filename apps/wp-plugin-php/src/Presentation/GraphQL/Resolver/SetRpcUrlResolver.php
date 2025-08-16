<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Service\ChainIdChecker;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Application\UseCase\UpdateRpcUrl;

class SetRpcUrlResolver extends ResolverBase {

	private UserAccessChecker $user_access_checker;
	private UpdateRpcUrl $update_rpc_url;
	private ChainIdChecker $chain_id_checker;

	public function __construct(
		UserAccessChecker $user_access_checker,
		UpdateRpcUrl $update_rpc_url,
		ChainIdChecker $chain_id_checker
	) {
		$this->user_access_checker = $user_access_checker;
		$this->update_rpc_url      = $update_rpc_url;
		$this->chain_id_checker    = $chain_id_checker;
	}

	/**
	 * #[\Override]
	 *
	 * @return bool
	 */
	public function resolve( array $root_value, array $args ) {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		/** @var int */
		$chain_id_value = $args['chainId'];
		/** @var string|null */
		$rpc_url_value = $args['rpcURL'];

		// 事前にRPC URLが登録しようとしているチェーンIDと一致するかどうかを確認
		if ( ! is_null( $rpc_url_value ) ) {
			$this->chain_id_checker->checkChainId( $rpc_url_value, $chain_id_value );
		}

		// RPC URLを保存
		$this->update_rpc_url->handle( $chain_id_value, $rpc_url_value );

		return true;
	}
}
