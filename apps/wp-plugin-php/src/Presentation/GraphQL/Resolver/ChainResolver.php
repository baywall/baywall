<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Dto\TokenDto;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Application\UseCase\GetAppContractDto;
use Cornix\Serendipity\Core\Application\UseCase\GetChainDto;
use Cornix\Serendipity\Core\Application\UseCase\GetTokensByChainId;

class ChainResolver extends ResolverBase {

	public function __construct(
		GetChainDto $get_chain_dto,
		GetAppContractDto $get_app_contract_dto,
		GetTokensByChainId $get_tokens_by_chain_id,
		UserAccessChecker $user_access_checker
	) {
		$this->get_chain_dto          = $get_chain_dto;
		$this->get_app_contract_dto   = $get_app_contract_dto;
		$this->get_tokens_by_chain_id = $get_tokens_by_chain_id;
		$this->user_access_checker    = $user_access_checker;
	}

	private GetChainDto $get_chain_dto;
	private GetAppContractDto $get_app_contract_dto;
	private GetTokensByChainId $get_tokens_by_chain_id;
	private UserAccessChecker $user_access_checker;

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$chain_id = $args['chainID'];

		$chain_dto = $this->get_chain_dto->handle( $chain_id );
		assert( null !== $chain_dto, '[CA31D9B5] chain data is not found. chain id: ' . $chain_id );

		// `AppContractResolver`の作成を省略してコールバックを定義
		// `AppContractResolver`を作成した場合はここの処理を書き換えること。
		$app_contract_callback = function () use ( $chain_dto ) {
			// 権限チェック不要
			$app_contract_dto = $this->get_app_contract_dto->handle( $chain_dto->id );
			$address          = null !== $app_contract_dto ? $app_contract_dto->address : null;
			return is_null( $address ) ? null : array( 'address' => $address );
		};

		$tokens_callback = function () use ( $root_value, $chain_id ) {
			$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

			$tokens = $this->get_tokens_by_chain_id->handle( $chain_id );

			return array_map(
				function ( TokenDto $token ) use ( $root_value, $chain_id ) {
					return $root_value['token'](
						$root_value,
						array(
							'chainID' => $chain_id,
							'address' => $token->address(),
						)
					);
				},
				$tokens
			);
		};

		$network_category_callback = function () use ( $root_value, $chain_dto ) {
			$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

			return $root_value['networkCategory'](
				$root_value,
				array(
					'networkCategoryID' => $chain_dto->network_category_id,
				)
			);
		};

		return array(
			'id'              => $chain_dto->id,
			'appContract'     => $app_contract_callback,
			'confirmations'   => $chain_dto->confirmations,
			'rpcURL'          => $chain_dto->rpc_url,
			'tokens'          => $tokens_callback,
			'networkCategory' => $network_category_callback,
		);
	}
}
