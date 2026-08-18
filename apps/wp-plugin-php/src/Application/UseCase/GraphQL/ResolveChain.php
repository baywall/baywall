<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase\GraphQL;

use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Domain\Entity\Token;
use Baywall\Core\Domain\Repository\AppContractRepository;
use Baywall\Core\Domain\Repository\ChainRepository;
use Baywall\Core\Domain\Repository\TokenRepository;
use Baywall\Core\Domain\Specification\TokensFilter;
use Baywall\Core\Domain\ValueObject\ChainId;

class ResolveChain {

	private UserAccessChecker $user_access_checker;
	private ChainRepository $chain_repository;
	private TokenRepository $token_repository;
	private AppContractRepository $app_contract_repository;

	public function __construct(
		UserAccessChecker $user_access_checker,
		ChainRepository $chain_repository,
		TokenRepository $token_repository,
		AppContractRepository $app_contract_repository
	) {
		$this->user_access_checker     = $user_access_checker;
		$this->chain_repository        = $chain_repository;
		$this->token_repository        = $token_repository;
		$this->app_contract_repository = $app_contract_repository;
	}

	public function handle( array $root_value, array $args ) {
		$chain_id = ChainId::from( $args['chainId'] );

		$chain = $this->chain_repository->get( $chain_id );
		assert( null !== $chain, "[CA31D9B5] chain data is not found. chain id: {$chain_id}" );

		// `AppContractResolver`の作成を省略してコールバックを定義
		// `AppContractResolver`を作成した場合はここの処理を書き換えること。
		$app_contract_callback = function () use ( $chain ) {
			// 権限チェック不要
			$app_contract = $this->app_contract_repository->get( $chain->id() );
			return $app_contract === null ? null : array( 'address' => $app_contract->address()->value() );
		};

		$tokens_callback = function () use ( $root_value, $chain_id ) {
			$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

			$filtered_tokens = ( new TokensFilter() )
			->byChainId( $chain_id )
			->apply( $this->token_repository->all() );

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
			return $chain->maxLogsRange();
		};

		return array(
			'id'               => $chain->id()->value(),
			'name'             => $chain->name(),
			'appContract'      => $app_contract_callback,
			'confirmations'    => $chain->confirmations()->value(),
			'rpcUrl'           => $chain->rpcUrl() ? $chain->rpcUrl()->value() : null,
			'tokens'           => $tokens_callback,
			'networkCategory'  => $network_category_callback,
			'blockExplorerUrl' => $chain->blockExplorerUrl(),
			'connectable'      => $connectable_callback,
			'maxLogsRange'     => $max_logs_range_callback,
		);
	}
}
