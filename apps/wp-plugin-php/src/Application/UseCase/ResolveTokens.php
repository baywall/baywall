<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Dto\TokenDto;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Application\UseCase\GetTokenDtosByFilter;

class ResolveTokens {

	private UserAccessChecker $user_access_checker;
	private GetTokenDtosByFilter $get_token_dtos_by_filter;

	public function __construct(
		UserAccessChecker $user_access_checker,
		GetTokenDtosByFilter $get_token_dtos_by_filter
	) {
		$this->user_access_checker      = $user_access_checker;
		$this->get_token_dtos_by_filter = $get_token_dtos_by_filter;
	}

	/**
	 * サイトに登録されているトークン一覧を取得します。
	 *
	 * ネイティブトークン + 管理者が追加したERC20トークンの一覧
	 */
	public function handle( array $root_value, array $args ) {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		$filter_chain_id_value = $args['filter']['chainId'] ?? null;
		$filter_address_value  = $args['filter']['address'] ?? null;

		return array_map(
			fn( TokenDto $token_dto ) => $root_value['token'](
				$root_value,
				array(
					'chainId' => $token_dto->chain_id,
					'address' => $token_dto->address,
				)
			),
			$this->get_token_dtos_by_filter->handle( $filter_chain_id_value, $filter_address_value )
		);
	}
}
