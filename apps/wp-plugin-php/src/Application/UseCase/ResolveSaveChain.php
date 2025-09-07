<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Application\UseCase\SaveChain;

class ResolveSaveChain {

	private UserAccessChecker $user_access_checker;
	private SaveChain $save_chain;

	public function __construct(
		UserAccessChecker $user_access_checker,
		SaveChain $save_chain
	) {
		$this->user_access_checker = $user_access_checker;
		$this->save_chain          = $save_chain;
	}

	public function handle( array $root_value, array $args ) {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		/** @var int */
		$chain_id_value = $args['chainId'];
		/** @var string|null */
		$rpc_url_value = $args['rpcUrl'];
		/** @var string */
		$confirmations_value = $args['confirmations'];

		// チェーン情報を保存
		$this->save_chain->handle( $chain_id_value, $rpc_url_value, $confirmations_value );

		return true;
	}
}
