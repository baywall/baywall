<?php
declare(strict_types=1);

namespace Baywall\Core\Application\UseCase;

use Baywall\Core\Application\Service\UserAccessChecker;
use Baywall\Core\Domain\Entity\Token;
use Baywall\Core\Domain\Repository\TokenRepository;
use Baywall\Core\Domain\Specification\TokensSpecification;
use Baywall\Core\Domain\ValueObject\Address;
use Baywall\Core\Domain\ValueObject\ChainId;

class GetTokens {
	public function __construct(
		private readonly UserAccessChecker $user_access_checker,
		private readonly TokenRepository $token_repository
	) {}

	/**
	 * サイトに登録されているトークン一覧を取得します。
	 *
	 * ネイティブトークン + 管理者が追加したERC20トークンの一覧
	 *
	 * @return Token[]
	 */
	public function handle( ?ChainId $chain_id, ?Address $address ): array {
		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		return array_filter(
			$this->token_repository->all(),
			( new TokensSpecification( chain_id: $chain_id, address: $address ) )->isSatisfiedBy( ... )
		);
	}
}
