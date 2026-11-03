<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Dto\NativeTokenDto;
use Baywall\Core\Application\Port\NativeTokenProvider;
use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Domain\ValueObject\ChainId;

class GetNativeToken {
	public function __construct(
		private readonly UserAccessChecker $user_access_checker,
		private readonly NativeTokenProvider $native_token_provider
	) {}

	public function handle( ChainId $chain_id ): NativeTokenDto {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		return new NativeTokenDto(
			$this->native_token_provider->getSymbol( $chain_id ),
			$this->native_token_provider->getName( $chain_id ),
			$this->native_token_provider->getDecimals( $chain_id )
		);
	}
}
