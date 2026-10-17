<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Domain\Entity\Token;
use Baywall\Core\Domain\Repository\TokenRepository;
use Baywall\Core\Domain\Specification\TokensSpecification;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\ChainId;

class ResolveTokens {


	public function __construct(
		private readonly UserAccessChecker $user_access_checker,
		private readonly TokenRepository $token_repository
	) {}

	/**
	 * サイトに登録されているトークン一覧を取得します。
	 *
	 * ネイティブトークン + 管理者が追加したERC20トークンの一覧
	 */
	public function handle( array $root_value, array $args ) {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		$filter_chain_id = ChainId::fromNullable( $args['filter']['chainId'] ?? null );
		$filter_address  = Address::fromNullable( $args['filter']['address'] ?? null );

		$filtered_tokens = array_filter(
			$this->token_repository->all(),
			( new TokensSpecification( chain_id: $filter_chain_id, address: $filter_address ) )->isSatisfiedBy( ... )
		);

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
	}
}
