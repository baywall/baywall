<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Infrastructure\Web3\Service\NativeTokenService;

class ResolveNativeToken {

	private UserAccessChecker $user_access_checker;
	private NativeTokenService $native_token_service;

	public function __construct( UserAccessChecker $user_access_checker, NativeTokenService $native_token_service ) {
		$this->user_access_checker  = $user_access_checker;
		$this->native_token_service = $native_token_service;
	}

	public function handle( array $root_value, array $args ): array {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		$chain_id = ChainId::from( $args['chainId'] );

		return array(
			'symbol'   => $this->native_token_service->getSymbol( $chain_id )->value(),
			'name'     => $this->native_token_service->getName( $chain_id ),
			'decimals' => $this->native_token_service->getDecimals( $chain_id )->value(),
		);
	}
}
