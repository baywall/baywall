<?php
declare(strict_types=1);

namespace Baywall\Core\Presentation\GraphQL\Resolvers;

use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Application\UseCase\GetToken;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\ChainId;

class ResolveToken implements Resolver {
	public function __construct(
		private readonly UserAccessChecker $user_access_checker,
		private readonly GetToken $get_token
	) {}

	/** @return array<string, mixed> */
	public function handle( array $root_value, array $args ) {
		$token = $this->get_token->handle(
			ChainId::from( $args['chainId'] ),
			Address::from( $args['address'] )
		);

		$decimals_callback = function () use ( $token ) {
			$this->user_access_checker->checkCanCreatePost(); // 投稿編集者の権限が必要
			return $token->decimals()->value();
		};

		$is_payable_callback = function () use ( $token ) {
			$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要
			return $token->isPayable();
		};

		return array(
			'chain'     => fn() => $root_value['chain']( $root_value, array( 'chainId' => $token->chainId()->value() ) ),
			'address'   => $token->address()->value(),
			'symbol'    => $token->symbol()->value(),
			'decimals'  => $decimals_callback,
			'isPayable' => $is_payable_callback,
		);
	}
}
