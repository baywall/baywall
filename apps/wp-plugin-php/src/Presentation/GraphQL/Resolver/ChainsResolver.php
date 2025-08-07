<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Dto\ChainDto;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Application\UseCase\GetChainDtosByFilter;

class ChainsResolver extends ResolverBase {

	public function __construct(
		GetChainDtosByFilter $get_chains_by_filter,
		UserAccessChecker $user_access_checker
	) {
		$this->get_chain_dtos_by_filter = $get_chains_by_filter;
		$this->user_access_checker      = $user_access_checker;
	}

	private GetChainDtosByFilter $get_chain_dtos_by_filter;
	private UserAccessChecker $user_access_checker;

	/**
	 * チェーン一覧を取得します。
	 *
	 * @inheritdoc
	 * @return array
	 */
	public function resolve( array $root_value, array $args ): array {
		$this->user_access_checker->checkHasAdminRole();  // 管理者権限が必要

		/** @var int|null */
		$filter_chain_id_value = $args['filter']['chainID'] ?? null;
		/** @var bool|null */
		$filter_is_connectable = $args['filter']['isConnectable'] ?? null;

		return array_map(
			fn( ChainDto $chain_dto ) => $root_value['chain'](
				$root_value,
				array(
					'chainID' => $chain_dto->id,
				)
			),
			$this->get_chain_dtos_by_filter->handle( $filter_chain_id_value, $filter_is_connectable )
		);
	}
}
