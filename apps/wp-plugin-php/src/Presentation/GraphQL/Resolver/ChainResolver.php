<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Dto\TokenDto;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Application\UseCase\GetAppContractDto;
use Cornix\Serendipity\Core\Application\UseCase\GetChainDto;
use Cornix\Serendipity\Core\Application\UseCase\GetTokenDtosByFilter;

class ChainResolver extends ResolverBase {

	public function __construct(
		GetChainDto $get_chain_dto,
		GetAppContractDto $get_app_contract_dto,
		GetTokenDtosByFilter $get_token_dtos_by_chain_id_value,
		UserAccessChecker $user_access_checker
	) {
		$this->get_chain_dto                    = $get_chain_dto;
		$this->get_app_contract_dto             = $get_app_contract_dto;
		$this->get_token_dtos_by_chain_id_value = $get_token_dtos_by_chain_id_value;
		$this->user_access_checker              = $user_access_checker;
	}

	private GetChainDto $get_chain_dto;
	private GetAppContractDto $get_app_contract_dto;
	private GetTokenDtosByFilter $get_token_dtos_by_chain_id_value;
	private UserAccessChecker $user_access_checker;

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$chain_id_value = $args['chainId'];

		$chain_dto = $this->get_chain_dto->handle( $chain_id_value );
		assert( null !== $chain_dto, "[CA31D9B5] chain data is not found. chain id: {$chain_id_value}" );

		// `AppContractResolver`の作成を省略してコールバックを定義
		// `AppContractResolver`を作成した場合はここの処理を書き換えること。
		$app_contract_callback = function () use ( $chain_dto ) {
			// 権限チェック不要
			$app_contract_dto = $this->get_app_contract_dto->handle( $chain_dto->id );
			return $app_contract_dto === null ? null : array( 'address' => $app_contract_dto->address );
		};

		$tokens_callback = function () use ( $root_value, $chain_id_value ) {
			$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

			return array_map(
				fn( TokenDto $token_dto ) => $root_value['token'](
					$root_value,
					array(
						'chainId' => $token_dto->chain_id,
						'address' => $token_dto->address,
					)
				),
				$this->get_token_dtos_by_chain_id_value->handle( $chain_id_value, null )
			);
		};

		$network_category_callback = function () use ( $root_value, $chain_dto ) {
			$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

			return $root_value['networkCategory'](
				$root_value,
				array(
					'networkCategoryId' => $chain_dto->network_category_id,
				)
			);
		};

		return array(
			'id'               => $chain_dto->id,
			'name'             => $chain_dto->name,
			'appContract'      => $app_contract_callback,
			'confirmations'    => $chain_dto->confirmations,
			'rpcUrl'           => $chain_dto->rpc_url,
			'tokens'           => $tokens_callback,
			'networkCategory'  => $network_category_callback,
			'blockExplorerUrl' => $chain_dto->block_explorer_url,
		);
	}
}
