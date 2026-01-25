<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase\GraphQL;

use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainId;
use Cornix\Serendipity\Core\Infrastructure\Web3\Constants\NativeTokenConstants;

class ResolveNativeToken {

	private UserAccessChecker $user_access_checker;

	public function __construct( UserAccessChecker $user_access_checker ) {
		$this->user_access_checker = $user_access_checker;
	}

	public function handle( array $root_value, array $args ): array {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		$chain_id = ChainId::from( $args['chainId'] );

		$def = NativeTokenConstants::DEFINITIONS[ $chain_id->value() ] ?? null;
		if ( $def === null ) {
			throw new \InvalidArgumentException( "[5A405D04] Native token is not defined for chain id: {$chain_id}" );
		}

		return array(
			'symbol'   => $def['symbol'],
			'name'     => $def['name'],
			'decimals' => $def['decimals'],
		);
	}
}
