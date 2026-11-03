<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Application\UseCase\GetAppContract;
use Baywall\Core\Application\UseCase\GetChain;
use Baywall\Core\Application\UseCase\GetTokens;
use Baywall\Core\Domain\Entity\Token;
use Baywall\Core\Domain\ValueObject\ChainId;

class ResolveChain implements Resolver {
	public function __construct(
		private readonly UserAccessChecker $user_access_checker,
		private readonly GetChain $get_chain,
		private readonly GetAppContract $get_app_contract,
		private readonly GetTokens $get_tokens
	) {}

	/** @return array<string, mixed> */
	public function handle( array $root_value, array $args ) {
		$chain_id = ChainId::from( $args['chainId'] );

		$chain = $this->get_chain->handle( $chain_id );

		// `AppContractResolver`の作成を省略してコールバックを定義
		// `AppContractResolver`を作成した場合はここの処理を書き換えること。
		$app_contract_callback = function () use ( $chain ) {
			// 権限チェック不要
			$app_contract = $this->get_app_contract->handle( $chain->id() );
			return $app_contract === null ? null : array( 'address' => $app_contract->address()->value() );
		};

		$tokens_callback = function () use ( $root_value, $chain_id ) {
			$filtered_tokens = $this->get_tokens->handle( $chain_id, null );

			return array_map(
				fn( Token $token ) => $root_value['token'](
					$root_value,
					array(
						'chainId' => $token->chainId()->value(),
						'address' => $token->address()->value(),
					)
				),
				$filtered_tokens
			);
		};

		$network_category_callback = function () use ( $root_value, $chain ) {
			$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

			return $root_value['networkCategory'](
				$root_value,
				array(
					'networkCategoryId' => $chain->networkCategoryId()->value(),
				)
			);
		};

		$connectable_callback = function () use ( $chain ) {
			$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要
			return $chain->connectable();
		};

		$max_logs_range_callback = function () use ( $chain ) {
			$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要
			return $chain->maxLogsRange()->value();
		};

		$has_reached_consecutive_failure_limit_callback = function () use ( $chain ) {
			$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要
			return $chain->hasReachedConsecutiveFailureLimit();
		};

		$rpc_url_callback = function () use ( $chain ) {
			$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要
			return $chain->rpcUrl()?->value();
		};

		return array(
			'id'                                => $chain->id()->value(),
			'name'                              => $chain->name(),
			'appContract'                       => $app_contract_callback,
			'confirmations'                     => $chain->confirmations()->value(),
			'rpcUrl'                            => $rpc_url_callback,
			'tokens'                            => $tokens_callback,
			'networkCategory'                   => $network_category_callback,
			'blockExplorerUrl'                  => $chain->blockExplorerUrl(),
			'connectable'                       => $connectable_callback,
			'maxLogsRange'                      => $max_logs_range_callback,
			'hasReachedConsecutiveFailureLimit' => $has_reached_consecutive_failure_limit_callback,
		);
	}
}
