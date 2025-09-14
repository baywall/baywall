<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Domain\Entity\Token;
use Cornix\Serendipity\Core\Domain\Repository\TokenRepository;
use Cornix\Serendipity\Core\Domain\Specification\TokensFilter;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;

class ResolveTokens {

	private UserAccessChecker $user_access_checker;
	private TokenRepository $token_repository;

	public function __construct(
		UserAccessChecker $user_access_checker,
		TokenRepository $token_repository
	) {
		$this->user_access_checker = $user_access_checker;
		$this->token_repository    = $token_repository;
	}

	/**
	 * サイトに登録されているトークン一覧を取得します。
	 *
	 * ネイティブトークン + 管理者が追加したERC20トークンの一覧
	 */
	public function handle( array $root_value, array $args ) {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		$filter_chain_id = ChainId::fromNullableValue( $args['filter']['chainId'] ?? null );
		$filter_address  = Address::fromNullable( $args['filter']['address'] ?? null );

		$filter = new TokensFilter();
		if ( $filter_chain_id !== null ) {
			$filter = $filter->byChainId( $filter_chain_id );
		}
		if ( $filter_address !== null ) {
			$filter = $filter->byAddress( $filter_address );
		}
		$filtered_tokens = $filter->apply( $this->token_repository->all() );

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
